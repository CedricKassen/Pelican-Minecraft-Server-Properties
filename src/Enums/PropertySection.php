<?php

namespace CedricKassen\MinecraftServerProperties\Enums;

use Filament\Support\Contracts\HasIcon;
use Filament\Support\Contracts\HasLabel;

enum PropertySection: string implements HasIcon, HasLabel
{
    case General = 'general';
    case Players = 'players';
    case World = 'world';
    case Performance = 'performance';
    case ResourcePack = 'resource_pack';
    case Advanced = 'advanced';

    public function getLabel(): string
    {
        return trans('minecraft-server-properties::strings.sections.' . $this->value . '.label');
    }

    public function getDescription(): string
    {
        return trans('minecraft-server-properties::strings.sections.' . $this->value . '.description');
    }

    public function getIcon(): string
    {
        return match ($this) {
            self::General => 'tabler-settings',
            self::Players => 'tabler-users',
            self::World => 'tabler-world',
            self::Performance => 'tabler-gauge',
            self::ResourcePack => 'tabler-photo',
            self::Advanced => 'tabler-adjustments',
        };
    }

    public function isCollapsedByDefault(): bool
    {
        return $this === self::ResourcePack || $this === self::Advanced;
    }
}
