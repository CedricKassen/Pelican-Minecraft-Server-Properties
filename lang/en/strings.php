<?php

return [
    'title' => 'Server Properties',

    'sections' => [
        'general' => [
            'label' => 'General',
            'description' => 'How your server presents itself and how it plays.',
        ],
        'players' => [
            'label' => 'Players & Access',
            'description' => 'Who can join and what players are allowed to do.',
        ],
        'world' => [
            'label' => 'World',
            'description' => 'The world that is loaded and how it is generated.',
        ],
        'performance' => [
            'label' => 'Performance',
            'description' => 'Lower values reduce CPU and network load.',
        ],
        'resource_pack' => [
            'label' => 'Resource Pack',
            'description' => 'A resource pack players are offered when joining.',
        ],
        'advanced' => [
            'label' => 'Advanced',
            'description' => 'Settings you rarely need to change.',
        ],
    ],

    'properties' => [
        'motd' => [
            'label' => 'Message of the Day',
            'helper' => 'Shown below the server name in the server list. Up to two lines.',
        ],
        'max-players' => [
            'label' => 'Max Players',
        ],
        'difficulty' => [
            'label' => 'Difficulty',
            'options' => [
                'peaceful' => 'Peaceful',
                'easy' => 'Easy',
                'normal' => 'Normal',
                'hard' => 'Hard',
            ],
        ],
        'gamemode' => [
            'label' => 'Default Game Mode',
            'options' => [
                'survival' => 'Survival',
                'creative' => 'Creative',
                'adventure' => 'Adventure',
                'spectator' => 'Spectator',
            ],
        ],
        'force-gamemode' => [
            'label' => 'Force Game Mode',
            'helper' => 'Put players into the default game mode every time they join.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'helper' => 'Players are put into spectator mode when they die.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'helper' => 'Allow players to damage each other.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'helper' => 'Only players on the whitelist can join.',
        ],
        'enforce-whitelist' => [
            'label' => 'Enforce Whitelist',
            'helper' => 'Kick players that are not whitelisted when the whitelist is reloaded.',
        ],
        'online-mode' => [
            'label' => 'Online Mode',
            'helper' => 'Verify players with Mojang. Only disable this behind a proxy, otherwise anyone can join with any name.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Enforce Secure Profile',
            'helper' => 'Require players to have a Mojang-signed key for chat messages.',
        ],
        'allow-flight' => [
            'label' => 'Allow Flight',
            'helper' => 'Don\'t kick players for flying in survival mode, needed by some mods and plugins.',
        ],
        'hide-online-players' => [
            'label' => 'Hide Online Players',
            'helper' => 'Don\'t show the list of online players in the server list.',
        ],
        'player-idle-timeout' => [
            'label' => 'Idle Timeout',
            'helper' => 'Kick players that are idle for this long. 0 disables it.',
            'suffix' => 'minutes',
        ],
        'spawn-protection' => [
            'label' => 'Spawn Protection Radius',
            'helper' => 'Only operators can build within this radius around the spawn. 0 disables it.',
            'suffix' => 'blocks',
        ],
        'op-permission-level' => [
            'label' => 'Operator Permission Level',
            'options' => [
                '1' => '1 - Bypass spawn protection',
                '2' => '2 - Use cheat commands',
                '3' => '3 - Manage players',
                '4' => '4 - Manage the server',
            ],
        ],
        'level-name' => [
            'label' => 'World Name',
            'helper' => 'Name of the world folder. A new world is generated if it doesn\'t exist.',
        ],
        'level-seed' => [
            'label' => 'World Seed',
            'helper' => 'Leave empty for a random seed.',
        ],
        'level-type' => [
            'label' => 'World Type',
            'options' => [
                'normal' => 'Normal',
                'flat' => 'Superflat',
                'large_biomes' => 'Large Biomes',
                'amplified' => 'Amplified',
                'single_biome_surface' => 'Single Biome',
            ],
        ],
        'generate-structures' => [
            'label' => 'Generate Structures',
            'helper' => 'Villages, temples, strongholds and so on.',
        ],
        'allow-nether' => [
            'label' => 'Allow Nether',
        ],
        'spawn-monsters' => [
            'label' => 'Spawn Monsters',
        ],
        'spawn-animals' => [
            'label' => 'Spawn Animals',
        ],
        'spawn-npcs' => [
            'label' => 'Spawn Villagers',
        ],
        'max-world-size' => [
            'label' => 'World Border Radius',
            'suffix' => 'blocks',
        ],
        'view-distance' => [
            'label' => 'View Distance',
            'helper' => 'How far players can see.',
            'suffix' => 'chunks',
        ],
        'simulation-distance' => [
            'label' => 'Simulation Distance',
            'helper' => 'How far from players entities move and crops grow.',
            'suffix' => 'chunks',
        ],
        'entity-broadcast-range-percentage' => [
            'label' => 'Entity Render Range',
            'helper' => 'How close entities have to be to be sent to players.',
            'suffix' => '%',
        ],
        'pause-when-empty-seconds' => [
            'label' => 'Pause When Empty',
            'helper' => 'Pause the server when no player was online for this long. 0 or less disables it.',
            'suffix' => 'seconds',
        ],
        'max-tick-time' => [
            'label' => 'Watchdog Timeout',
            'helper' => 'Stop the server if a single tick takes longer. -1 disables it.',
            'suffix' => 'ms',
        ],
        'network-compression-threshold' => [
            'label' => 'Network Compression Threshold',
            'helper' => 'Compress packets bigger than this. -1 disables compression.',
            'suffix' => 'bytes',
        ],
        'sync-chunk-writes' => [
            'label' => 'Synchronous Chunk Writes',
            'helper' => 'Safer against world corruption, but slower.',
        ],
        'resource-pack' => [
            'label' => 'Resource Pack URL',
            'helper' => 'A direct download link to a .zip file.',
        ],
        'resource-pack-sha1' => [
            'label' => 'Resource Pack SHA-1',
            'helper' => 'Lets clients verify the download and cache it.',
        ],
        'resource-pack-prompt' => [
            'label' => 'Resource Pack Prompt',
            'helper' => 'Custom message shown when players are asked to download it.',
        ],
        'require-resource-pack' => [
            'label' => 'Require Resource Pack',
            'helper' => 'Kick players that decline the resource pack.',
        ],
        'enable-command-block' => [
            'label' => 'Command Blocks',
        ],
        'function-permission-level' => [
            'label' => 'Function Permission Level',
            'options' => [
                '1' => '1 - Bypass spawn protection',
                '2' => '2 - Use cheat commands',
                '3' => '3 - Manage players',
                '4' => '4 - Manage the server',
            ],
        ],
        'broadcast-console-to-ops' => [
            'label' => 'Broadcast Console to Operators',
        ],
        'enable-status' => [
            'label' => 'Show in Server List',
            'helper' => 'When disabled the server appears offline, but players can still join.',
        ],
        'accepts-transfers' => [
            'label' => 'Accept Transfers',
            'helper' => 'Allow players to be transferred here from other servers.',
        ],
        'log-ips' => [
            'label' => 'Log IP Addresses',
        ],
    ],

    'motd' => [
        'toolbar' => [
            'label' => 'Formatting',
            'undo' => 'Undo',
            'redo' => 'Redo',
            'color' => 'Text color',
            'default_color' => 'Default (gray)',
            'symbols' => 'Insert symbol',
            'source' => 'Edit source with § codes',
        ],
        'symbols' => [
            'arrows' => 'Arrows',
            'corner_arrows' => 'Corner arrows',
            'lines' => 'Lines & frames',
            'symbols' => 'Symbols',
        ],
        'line_limit' => 'The server list shows two lines at most.',
        'codes' => [
            '0' => 'Black',
            '1' => 'Dark Blue',
            '2' => 'Dark Green',
            '3' => 'Dark Aqua',
            '4' => 'Dark Red',
            '5' => 'Dark Purple',
            '6' => 'Gold',
            '7' => 'Gray',
            '8' => 'Dark Gray',
            '9' => 'Blue',
            'a' => 'Green',
            'b' => 'Aqua',
            'c' => 'Red',
            'd' => 'Light Purple',
            'e' => 'Yellow',
            'f' => 'White',
            'k' => 'Obfuscated',
            'l' => 'Bold',
            'm' => 'Strikethrough',
            'n' => 'Underline',
            'o' => 'Italic',
            'r' => 'Reset',
        ],
    ],

    'callouts' => [
        'missing_title' => 'No server.properties found',
        'missing_body' => 'The file is created when the server is started for the first time. Start your server once and come back.',
        'not_populated_title' => 'server.properties is not complete yet',
        'not_populated_body' => 'Minecraft adds all settings to this file the first time the server starts. Start (or restart) your server and reload this page to edit them, until then only the settings already in the file are shown.',
        'load_failed_title' => 'Could not load server.properties',
        'load_failed_body' => 'The node could not be reached. Please try again later.',
        'restart_title' => 'Your server is running',
        'restart_body' => 'Minecraft only reads these settings on startup, changes take effect after a restart.',
    ],

    'hints' => [
        'managed_by_egg' => 'Managed by the panel, it is set on every server start.',
        'locked' => 'Locked by an administrator.',
        'new_world_only' => 'Only applies when a new world is generated.',
    ],

    'actions' => [
        'edit_file' => 'Edit server.properties manually',
        'save' => 'Save',
        'save_and_restart' => 'Save & Restart',
    ],

    'modals' => [
        'restart_description' => 'Your changes are saved and the server is restarted. Players online will be disconnected.',
    ],

    'notifications' => [
        'saved' => 'Server properties saved',
        'saved_body' => 'Changed: :properties',
        'no_changes' => 'Nothing to save',
        'save_failed' => 'Saving failed',
        'save_failed_body' => 'The server.properties could not be written. Please try again or contact support if the issue persists.',
        'restarting' => 'Server is restarting',
        'restart_failed' => 'Restart failed',
        'restart_failed_body' => 'Your changes were saved, but the server could not be restarted.',
    ],

    'settings' => [
        'locked_properties' => 'Locked Properties',
        'locked_properties_helper' => 'Users can see these properties but not change them, e.g. max-players.',
        'saved' => 'Settings saved',
    ],
];
