<?php

namespace CedricKassen\MinecraftServerProperties\Properties;

use CedricKassen\MinecraftServerProperties\Enums\PropertySection;
use CedricKassen\MinecraftServerProperties\Enums\PropertyType;

/**
 * The properties shown in the form. A property is only shown if the server.properties of the
 * server actually contains it, so keys that were added or removed by a minecraft version
 * simply appear or disappear.
 *
 * Deliberately left out: server-ip, server-port and query.port (written by the egg from the
 * allocation on every start), everything rcon/jmx/management-server related (gives remote
 * control over the server and its secrets) and debug or JSON only settings.
 */
class PropertyRegistry
{
    /** @var array<string, PropertyDefinition>|null */
    private static ?array $definitions = null;

    /** @return array<string, PropertyDefinition> */
    public static function all(): array
    {
        return self::$definitions ??= collect(self::define())->keyBy(fn (PropertyDefinition $definition) => $definition->key)->all();
    }

    public static function get(string $key): ?PropertyDefinition
    {
        return self::all()[$key] ?? null;
    }

    /** @return array<int, PropertyDefinition> */
    private static function define(): array
    {
        $general = PropertySection::General;
        $players = PropertySection::Players;
        $world = PropertySection::World;
        $performance = PropertySection::Performance;
        $resourcePack = PropertySection::ResourcePack;
        $advanced = PropertySection::Advanced;

        $permissionLevels = ['1', '2', '3', '4'];

        return [
            new PropertyDefinition('motd', $general, PropertyType::Motd),
            new PropertyDefinition('max-players', $general, PropertyType::Integer, min: 1, max: 100000, required: true),
            new PropertyDefinition('difficulty', $general, PropertyType::Select, options: ['peaceful', 'easy', 'normal', 'hard'], required: true),
            new PropertyDefinition('gamemode', $general, PropertyType::Select, options: ['survival', 'creative', 'adventure', 'spectator'], required: true),
            new PropertyDefinition('force-gamemode', $general, PropertyType::Boolean),
            new PropertyDefinition('hardcore', $general, PropertyType::Boolean),
            new PropertyDefinition('pvp', $general, PropertyType::Boolean),

            new PropertyDefinition('white-list', $players, PropertyType::Boolean),
            new PropertyDefinition('enforce-whitelist', $players, PropertyType::Boolean),
            new PropertyDefinition('online-mode', $players, PropertyType::Boolean),
            new PropertyDefinition('enforce-secure-profile', $players, PropertyType::Boolean),
            new PropertyDefinition('allow-flight', $players, PropertyType::Boolean),
            new PropertyDefinition('hide-online-players', $players, PropertyType::Boolean),
            new PropertyDefinition('player-idle-timeout', $players, PropertyType::Integer, min: 0, required: true),
            new PropertyDefinition('spawn-protection', $players, PropertyType::Integer, min: 0, required: true),
            new PropertyDefinition('op-permission-level', $players, PropertyType::Select, options: $permissionLevels, required: true),

            new PropertyDefinition('level-name', $world, PropertyType::Text, regex: '/^(?!\.+$)[^\/\\\\:*?"<>|]+$/', required: true),
            new PropertyDefinition('level-seed', $world, PropertyType::Text, newWorldOnly: true),
            new PropertyDefinition('level-type', $world, PropertyType::Select, options: ['minecraft:normal', 'minecraft:flat', 'minecraft:large_biomes', 'minecraft:amplified', 'minecraft:single_biome_surface'], required: true, newWorldOnly: true),
            new PropertyDefinition('generate-structures', $world, PropertyType::Boolean, newWorldOnly: true),
            new PropertyDefinition('allow-nether', $world, PropertyType::Boolean),
            new PropertyDefinition('spawn-monsters', $world, PropertyType::Boolean),
            new PropertyDefinition('spawn-animals', $world, PropertyType::Boolean),
            new PropertyDefinition('spawn-npcs', $world, PropertyType::Boolean),
            new PropertyDefinition('max-world-size', $world, PropertyType::Integer, min: 1, max: 29999984, required: true),

            new PropertyDefinition('view-distance', $performance, PropertyType::Integer, min: 3, max: 32, required: true),
            new PropertyDefinition('simulation-distance', $performance, PropertyType::Integer, min: 3, max: 32, required: true),
            new PropertyDefinition('entity-broadcast-range-percentage', $performance, PropertyType::Integer, min: 10, max: 1000, required: true),
            new PropertyDefinition('pause-when-empty-seconds', $performance, PropertyType::Integer, min: -1, required: true),
            new PropertyDefinition('max-tick-time', $performance, PropertyType::Integer, min: -1, required: true),
            new PropertyDefinition('network-compression-threshold', $performance, PropertyType::Integer, min: -1, required: true),
            new PropertyDefinition('sync-chunk-writes', $performance, PropertyType::Boolean),

            new PropertyDefinition('resource-pack', $resourcePack, PropertyType::Text, regex: '/^(https?:\/\/\S+)?$/i'),
            new PropertyDefinition('resource-pack-sha1', $resourcePack, PropertyType::Text, regex: '/^([0-9a-f]{40})?$/i'),
            new PropertyDefinition('resource-pack-prompt', $resourcePack, PropertyType::Text),
            new PropertyDefinition('require-resource-pack', $resourcePack, PropertyType::Boolean),

            new PropertyDefinition('enable-command-block', $advanced, PropertyType::Boolean),
            new PropertyDefinition('function-permission-level', $advanced, PropertyType::Select, options: $permissionLevels, required: true),
            new PropertyDefinition('broadcast-console-to-ops', $advanced, PropertyType::Boolean),
            new PropertyDefinition('enable-status', $advanced, PropertyType::Boolean),
            new PropertyDefinition('accepts-transfers', $advanced, PropertyType::Boolean),
            new PropertyDefinition('log-ips', $advanced, PropertyType::Boolean),
        ];
    }
}
