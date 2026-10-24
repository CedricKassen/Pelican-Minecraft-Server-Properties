<?php

namespace CedricKassen\MinecraftServerProperties;

use App\Contracts\Plugins\HasPluginSettings;
use App\Traits\EnvironmentWriterTrait;
use CedricKassen\MinecraftServerProperties\Properties\PropertyRegistry;
use Filament\Contracts\Plugin;
use Filament\Forms\Components\TagsInput;
use Filament\Notifications\Notification;
use Filament\Panel;

class MinecraftServerPropertiesPlugin implements HasPluginSettings, Plugin
{
    use EnvironmentWriterTrait;

    public function getId(): string
    {
        return 'minecraft-server-properties';
    }

    public function register(Panel $panel): void
    {
        $id = str($panel->getId())->title();

        $panel->discoverPages(plugin_path($this->getId(), "src/Filament/$id/Pages"), "CedricKassen\\MinecraftServerProperties\\Filament\\$id\\Pages");
    }

    public function boot(Panel $panel): void {}

    public function getSettingsFormData(): array
    {
        return config('minecraft-server-properties');
    }

    public function getSettingsForm(): array
    {
        return [
            TagsInput::make('locked_properties')
                ->label(trans('minecraft-server-properties::strings.settings.locked_properties'))
                ->helperText(trans('minecraft-server-properties::strings.settings.locked_properties_helper'))
                ->suggestions(array_keys(PropertyRegistry::all()))
                ->default(fn () => config('minecraft-server-properties.locked_properties')),
        ];
    }

    public function saveSettings(array $data): void
    {
        $this->writeToEnvironment([
            'MINECRAFT_SERVER_PROPERTIES_LOCKED' => implode(',', array_map('trim', $data['locked_properties'] ?? [])),
        ]);

        Notification::make()
            ->title(trans('minecraft-server-properties::strings.settings.saved'))
            ->success()
            ->send();
    }
}
