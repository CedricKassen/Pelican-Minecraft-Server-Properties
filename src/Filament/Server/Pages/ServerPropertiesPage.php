<?php

namespace CedricKassen\MinecraftServerProperties\Filament\Server\Pages;

use App\Enums\SubuserPermission;
use App\Facades\Activity;
use App\Filament\Server\Pages\ServerFormPage;
use App\Filament\Server\Resources\Files\Pages\EditFiles;
use App\Models\Server;
use App\Repositories\Daemon\DaemonServerRepository;
use CedricKassen\MinecraftServerProperties\Enums\PropertySection;
use CedricKassen\MinecraftServerProperties\Enums\PropertyType;
use CedricKassen\MinecraftServerProperties\Properties\MotdRenderer;
use CedricKassen\MinecraftServerProperties\Properties\PropertyDefinition;
use CedricKassen\MinecraftServerProperties\Properties\PropertyRegistry;
use CedricKassen\MinecraftServerProperties\Services\ServerPropertiesService;
use Exception;
use Filament\Actions\Action;
use Filament\Facades\Filament;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Schemas\Components\Callout;
use Filament\Schemas\Components\Component;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Get;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Support\Enums\Width;
use Illuminate\Support\HtmlString;

class ServerPropertiesPage extends ServerFormPage
{
    protected static string|\BackedEnum|null $navigationIcon = 'tabler-adjustments-horizontal';

    protected static ?string $slug = 'server-properties';

    // Right after the startup page.
    protected static ?int $navigationSort = 11;

    /** @var array<string, string> raw values of the known properties the file contains, as last read or saved */
    public array $presentValues = [];

    public bool $fileMissing = false;

    // The file exists, but minecraft didn't write its defaults into it yet.
    public bool $notPopulated = false;

    public bool $loadFailed = false;

    public static function canAccess(): bool
    {
        /** @var Server $server */
        $server = Filament::getTenant();

        return parent::canAccess()
            && ServerPropertiesService::isEnabledForServer($server)
            && user()?->can(SubuserPermission::FileReadContent, $server);
    }

    public static function getNavigationLabel(): string
    {
        return trans('minecraft-server-properties::strings.title');
    }

    public function getTitle(): string
    {
        return static::getNavigationLabel();
    }

    protected function fillForm(): void
    {
        try {
            $file = app(ServerPropertiesService::class)->read($this->getRecord());

            $this->fileMissing = $file === null;
            $this->notPopulated = $file !== null && !ServerPropertiesService::isPopulated($file);
            $this->presentValues = array_intersect_key($file?->all() ?? [], PropertyRegistry::all());
        } catch (Exception $exception) {
            report($exception);

            // Don't show an empty form for an unreachable daemon, saving it would do nothing useful.
            $this->loadFailed = true;
            $this->presentValues = [];
        }

        $this->form->fill($this->getOriginalState());
    }

    /** @return array<string, PropertyDefinition> */
    protected function getPresentDefinitions(): array
    {
        return array_intersect_key(PropertyRegistry::all(), $this->presentValues);
    }

    /** @return array<string, mixed> */
    protected function getOriginalState(): array
    {
        $state = [];

        foreach ($this->getPresentDefinitions() as $definition) {
            $state[$definition->getFieldName()] = $definition->toFormState($this->presentValues[$definition->key]);
        }

        return $state;
    }

    /**
     * Properties users can't change: written by the egg on every start or locked by an admin.
     *
     * @return array<int, string>
     */
    protected function getReadOnlyKeys(): array
    {
        return array_merge(ServerPropertiesService::getEggManagedKeys($this->getRecord()), ServerPropertiesService::getLockedKeys());
    }

    protected function canUpdate(): bool
    {
        return !$this->fileMissing && !$this->loadFailed && (user()?->can(SubuserPermission::FileUpdate, $this->getRecord()) ?? false);
    }

    public function form(Schema $schema): Schema
    {
        $managedKeys = ServerPropertiesService::getEggManagedKeys($this->getRecord());
        $lockedKeys = ServerPropertiesService::getLockedKeys();

        $sections = [];

        foreach (PropertySection::cases() as $section) {
            $fields = [];

            foreach ($this->getPresentDefinitions() as $definition) {
                if ($definition->section !== $section) {
                    continue;
                }

                $fields[] = $this->makeField($definition, $managedKeys, $lockedKeys);

                if ($definition->type === PropertyType::Motd) {
                    $fields[] = TextEntry::make('motd_preview')
                        ->label(trans('minecraft-server-properties::strings.motd.preview'))
                        ->state(fn (Get $get) => new HtmlString(MotdRenderer::toHtml($get($definition->getFieldName()))));
                }
            }

            if (empty($fields)) {
                continue;
            }

            $sections[] = Section::make($section->getLabel())
                ->description($section->getDescription())
                ->icon($section->getIcon())
                ->collapsible()
                ->collapsed($section->isCollapsedByDefault())
                ->columns([
                    'default' => 1,
                    'md' => 2,
                ])
                ->columnSpanFull()
                ->schema($fields);
        }

        return parent::form($schema)
            ->components([
                Callout::make(trans('minecraft-server-properties::strings.callouts.missing_title'))
                    ->description(trans('minecraft-server-properties::strings.callouts.missing_body'))
                    ->icon('tabler-file-alert')
                    ->warning()
                    ->visible(fn () => $this->fileMissing)
                    ->columnSpanFull(),
                Callout::make(trans('minecraft-server-properties::strings.callouts.not_populated_title'))
                    ->description(trans('minecraft-server-properties::strings.callouts.not_populated_body'))
                    ->icon('tabler-file-alert')
                    ->warning()
                    ->visible(fn () => $this->notPopulated)
                    ->columnSpanFull(),
                Callout::make(trans('minecraft-server-properties::strings.callouts.load_failed_title'))
                    ->description(trans('minecraft-server-properties::strings.callouts.load_failed_body'))
                    ->icon('tabler-plug-connected-x')
                    ->danger()
                    ->visible(fn () => $this->loadFailed)
                    ->columnSpanFull(),
                Callout::make(trans('minecraft-server-properties::strings.callouts.restart_title'))
                    ->description(trans('minecraft-server-properties::strings.callouts.restart_body'))
                    ->icon('tabler-reload')
                    ->info()
                    ->visible(fn () => !$this->fileMissing && !$this->notPopulated && !$this->loadFailed && $this->getRecord()->retrieveStatus()->isStartingOrRunning())
                    ->columnSpanFull(),
                ...$sections,
            ]);
    }

    /**
     * @param  array<int, string>  $managedKeys
     * @param  array<int, string>  $lockedKeys
     */
    protected function makeField(PropertyDefinition $definition, array $managedKeys, array $lockedKeys): Component
    {
        $name = $definition->getFieldName();
        $currentValue = $this->presentValues[$definition->key] ?? null;
        $isManaged = in_array($definition->key, $managedKeys);
        $isReadOnly = $isManaged || in_array($definition->key, $lockedKeys);

        $field = match ($definition->type) {
            PropertyType::Boolean => Toggle::make($name)
                ->inline(false),
            PropertyType::Integer => TextInput::make($name)
                ->integer()
                ->minValue($definition->min)
                ->maxValue($definition->max)
                ->suffix($definition->getSuffix()),
            PropertyType::Select => Select::make($name)
                ->options($definition->getOptions($currentValue))
                ->selectablePlaceholder(!$definition->required)
                ->native(false),
            PropertyType::Text => TextInput::make($name)
                ->regex($definition->regex),
            PropertyType::Motd => Textarea::make($name)
                ->rows(2)
                ->live(debounce: 500)
                ->hintAction($this->makeMotdFormatAction($name)->visible(fn () => $this->canUpdate() && !$isReadOnly)),
        };

        $field
            ->label($definition->getLabel())
            ->helperText($definition->getHelperText())
            ->required($definition->required)
            ->disabled(fn () => $isReadOnly || !$this->canUpdate());

        if ($isReadOnly) {
            $field
                ->hintIcon('tabler-lock')
                ->hintIconTooltip($isManaged
                    ? trans('minecraft-server-properties::strings.hints.managed_by_egg')
                    : trans('minecraft-server-properties::strings.hints.locked'));
        } elseif ($definition->newWorldOnly) {
            $field
                ->hintIcon('tabler-world-plus')
                ->hintIconTooltip(trans('minecraft-server-properties::strings.hints.new_world_only'));
        }

        return $field;
    }

    protected function makeMotdFormatAction(string $fieldName): Action
    {
        $options = [];

        foreach (MotdRenderer::COLORS as $code => $color) {
            $options[$code] = '<span style="color: ' . $color . '; text-shadow: 0 0 1px #000;">&#9632;</span> ' . e(trans('minecraft-server-properties::strings.motd.codes.' . $code));
        }

        foreach (array_keys(MotdRenderer::FORMATS) as $code) {
            $options[$code] = e(trans('minecraft-server-properties::strings.motd.codes.' . $code));
        }

        $options['r'] = e(trans('minecraft-server-properties::strings.motd.codes.r'));

        return Action::make('insert_motd_format')
            ->label(trans('minecraft-server-properties::strings.motd.insert_format'))
            ->icon('tabler-palette')
            ->modalWidth(Width::Small)
            ->schema([
                Select::make('code')
                    ->label(trans('minecraft-server-properties::strings.motd.format'))
                    ->options($options)
                    ->allowHtml()
                    ->native(false)
                    ->required(),
            ])
            ->action(fn (array $data, Get $get, Set $set) => $set($fieldName, ($get($fieldName) ?? '') . '§' . $data['code']));
    }

    /** @return array<Action> */
    protected function getDefaultHeaderActions(): array
    {
        return [
            Action::make('edit_file')
                ->hiddenLabel()
                ->tooltip(trans('minecraft-server-properties::strings.actions.edit_file'))
                ->icon('tabler-file-code')
                ->color('gray')
                ->visible(fn () => !$this->fileMissing && !$this->loadFailed)
                ->url(fn () => EditFiles::getUrl(['path' => ServerPropertiesService::FILE])),
            Action::make('save_and_restart')
                ->label(trans('minecraft-server-properties::strings.actions.save_and_restart'))
                ->icon('tabler-reload')
                ->color('gray')
                ->authorize(fn () => $this->canUpdate() && user()?->can(SubuserPermission::ControlRestart, $this->getRecord()))
                ->visible(fn () => $this->getRecord()->retrieveStatus()->isStartingOrRunning())
                ->requiresConfirmation()
                ->modalHeading(trans('minecraft-server-properties::strings.actions.save_and_restart'))
                ->modalDescription(trans('minecraft-server-properties::strings.modals.restart_description'))
                ->action(fn () => $this->saveAndRestart()),
            Action::make('save')
                ->label(trans('minecraft-server-properties::strings.actions.save'))
                ->icon('tabler-device-floppy')
                ->authorize(fn () => $this->canUpdate())
                ->keyBindings(['mod+s'])
                ->action('save'),
        ];
    }

    public function save(): void
    {
        $this->persist();
    }

    public function saveAndRestart(): void
    {
        $server = $this->getRecord();

        abort_unless(user()?->can(SubuserPermission::ControlRestart, $server), 403);

        if (!$this->persist()) {
            return;
        }

        try {
            app(DaemonServerRepository::class)->setServer($server)->power('restart')->throw();

            Notification::make()
                ->title(trans('minecraft-server-properties::strings.notifications.restarting'))
                ->success()
                ->send();
        } catch (Exception $exception) {
            report($exception);

            Notification::make()
                ->title(trans('minecraft-server-properties::strings.notifications.restart_failed'))
                ->body(trans('minecraft-server-properties::strings.notifications.restart_failed_body'))
                ->danger()
                ->send();
        }
    }

    /**
     * Writes the changed properties, and only those, to the server.
     */
    protected function persist(): bool
    {
        abort_unless($this->canUpdate(), 403);

        $state = $this->form->getState();
        $originalState = $this->getOriginalState();
        $readOnlyKeys = $this->getReadOnlyKeys();

        $changes = [];

        foreach ($this->getPresentDefinitions() as $definition) {
            $name = $definition->getFieldName();

            // Disabled fields aren't part of the state, the explicit check guards against crafted requests.
            if (in_array($definition->key, $readOnlyKeys) || !array_key_exists($name, $state)) {
                continue;
            }

            $value = $definition->toFileValue($state[$name]);

            // Compared normalized, so a "TRUE" in the file doesn't count as a change to "true".
            if ($value !== $definition->toFileValue($originalState[$name] ?? null)) {
                $changes[$definition->key] = $value;
            }
        }

        if (empty($changes)) {
            Notification::make()
                ->title(trans('minecraft-server-properties::strings.notifications.no_changes'))
                ->info()
                ->send();

            return true;
        }

        try {
            app(ServerPropertiesService::class)->write($this->getRecord(), $changes);
        } catch (Exception $exception) {
            report($exception);

            Notification::make()
                ->title(trans('minecraft-server-properties::strings.notifications.save_failed'))
                ->body(trans('minecraft-server-properties::strings.notifications.save_failed_body'))
                ->danger()
                ->send();

            return false;
        }

        Activity::event('server:file.write')
            ->property('file', ServerPropertiesService::FILE)
            ->log();

        $this->presentValues = array_merge($this->presentValues, $changes);

        Notification::make()
            ->title(trans('minecraft-server-properties::strings.notifications.saved'))
            ->body(trans('minecraft-server-properties::strings.notifications.saved_body', ['properties' => implode(', ', array_keys($changes))]))
            ->success()
            ->send();

        return true;
    }
}
