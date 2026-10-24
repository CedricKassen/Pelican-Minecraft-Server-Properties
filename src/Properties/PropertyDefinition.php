<?php

namespace CedricKassen\MinecraftServerProperties\Properties;

use CedricKassen\MinecraftServerProperties\Enums\PropertySection;
use CedricKassen\MinecraftServerProperties\Enums\PropertyType;
use Illuminate\Support\Facades\Lang;

class PropertyDefinition
{
    /**
     * @param  array<int, string>  $options  allowed values of a select, labels come from the lang file
     */
    public function __construct(
        public readonly string $key,
        public readonly PropertySection $section,
        public readonly PropertyType $type,
        public readonly ?int $min = null,
        public readonly ?int $max = null,
        public readonly array $options = [],
        public readonly ?string $regex = null,
        public readonly bool $required = false,
        public readonly bool $newWorldOnly = false,
    ) {}

    /**
     * Form field name, dots would be read as nesting by the form state.
     */
    public function getFieldName(): string
    {
        return str_replace(['-', '.'], '_', $this->key);
    }

    public function getLabel(): string
    {
        return $this->translate('label') ?? str($this->key)->replace(['-', '.'], ' ')->ucfirst()->toString();
    }

    public function getHelperText(): ?string
    {
        return $this->translate('helper');
    }

    public function getSuffix(): ?string
    {
        return $this->translate('suffix');
    }

    /** @return array<string, string> */
    public function getOptions(?string $currentValue = null): array
    {
        $options = [];

        foreach ($this->options as $option) {
            $options[$option] = $this->translate('options.' . str_replace(['minecraft:', '.'], ['', '_'], $option)) ?? $option;
        }

        // Older minecraft versions use other values (e.g. numeric difficulties), keep them selectable.
        if ($currentValue !== null && $currentValue !== '' && !array_key_exists($currentValue, $options)) {
            $options = [$currentValue => $currentValue] + $options;
        }

        return $options;
    }

    public function toFormState(?string $value): mixed
    {
        return match ($this->type) {
            PropertyType::Boolean => filter_var($value, FILTER_VALIDATE_BOOLEAN),
            PropertyType::Integer => is_numeric($value) ? (int) $value : null,
            default => $value ?? '',
        };
    }

    public function toFileValue(mixed $state): string
    {
        return match ($this->type) {
            PropertyType::Boolean => $state ? 'true' : 'false',
            PropertyType::Integer => is_numeric($state) ? (string) (int) $state : '',
            PropertyType::Motd => str_replace(["\r\n", "\r"], "\n", (string) $state),
            default => (string) $state,
        };
    }

    private function translate(string $suffix): ?string
    {
        $key = "minecraft-server-properties::strings.properties.$this->key.$suffix";

        return Lang::has($key) ? trans($key) : null;
    }
}
