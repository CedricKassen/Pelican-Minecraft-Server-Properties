<?php

namespace CedricKassen\MinecraftServerProperties\Forms\Components;

use Closure;
use Filament\Forms\Components\Field;

/**
 * Edits a motd the way it looks in the server list, with a toolbar for the § color and format codes.
 */
class MotdEditor extends Field
{
    protected string $view = 'minecraft-server-properties::components.motd-editor';

    protected string|Closure|null $serverName = null;

    protected int|string|Closure|null $maxPlayers = null;

    public function serverName(string|Closure|null $serverName): static
    {
        $this->serverName = $serverName;

        return $this;
    }

    public function getServerName(): ?string
    {
        return $this->evaluate($this->serverName);
    }

    public function maxPlayers(int|string|Closure|null $maxPlayers): static
    {
        $this->maxPlayers = $maxPlayers;

        return $this;
    }

    public function getMaxPlayers(): ?string
    {
        $maxPlayers = $this->evaluate($this->maxPlayers);

        return is_numeric($maxPlayers) ? (string) (int) $maxPlayers : null;
    }
}
