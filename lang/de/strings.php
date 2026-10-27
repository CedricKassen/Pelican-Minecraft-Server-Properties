<?php

return [
    'title' => 'Servereinstellungen',

    'sections' => [
        'general' => [
            'label' => 'Allgemein',
            'description' => 'Wie sich dein Server präsentiert und wie gespielt wird.',
        ],
        'players' => [
            'label' => 'Spieler & Zugang',
            'description' => 'Wer beitreten darf und was Spieler dürfen.',
        ],
        'world' => [
            'label' => 'Welt',
            'description' => 'Die geladene Welt und wie sie generiert wird.',
        ],
        'performance' => [
            'label' => 'Leistung',
            'description' => 'Niedrigere Werte reduzieren CPU- und Netzwerklast.',
        ],
        'resource_pack' => [
            'label' => 'Ressourcenpaket',
            'description' => 'Ein Ressourcenpaket, das Spielern beim Beitreten angeboten wird.',
        ],
        'advanced' => [
            'label' => 'Erweitert',
            'description' => 'Einstellungen, die du selten ändern musst.',
        ],
    ],

    'properties' => [
        'motd' => [
            'label' => 'Nachricht des Tages',
            'helper' => 'Wird in der Serverliste unter dem Servernamen angezeigt. Bis zu zwei Zeilen.',
        ],
        'max-players' => [
            'label' => 'Maximale Spieler',
        ],
        'difficulty' => [
            'label' => 'Schwierigkeit',
            'options' => [
                'peaceful' => 'Friedlich',
                'easy' => 'Einfach',
                'normal' => 'Normal',
                'hard' => 'Schwer',
            ],
        ],
        'gamemode' => [
            'label' => 'Standard-Spielmodus',
            'options' => [
                'survival' => 'Überleben',
                'creative' => 'Kreativ',
                'adventure' => 'Abenteuer',
                'spectator' => 'Zuschauer',
            ],
        ],
        'force-gamemode' => [
            'label' => 'Spielmodus erzwingen',
            'helper' => 'Spieler bei jedem Beitreten in den Standard-Spielmodus versetzen.',
        ],
        'hardcore' => [
            'label' => 'Hardcore',
            'helper' => 'Spieler werden nach dem Tod in den Zuschauermodus versetzt.',
        ],
        'pvp' => [
            'label' => 'PvP',
            'helper' => 'Spieler können sich gegenseitig Schaden zufügen.',
        ],
        'white-list' => [
            'label' => 'Whitelist',
            'helper' => 'Nur Spieler auf der Whitelist können beitreten.',
        ],
        'enforce-whitelist' => [
            'label' => 'Whitelist erzwingen',
            'helper' => 'Spieler ohne Whitelist-Eintrag beim Neuladen der Whitelist kicken.',
        ],
        'online-mode' => [
            'label' => 'Online-Modus',
            'helper' => 'Spieler bei Mojang verifizieren. Nur hinter einem Proxy deaktivieren, sonst kann jeder mit jedem Namen beitreten.',
        ],
        'enforce-secure-profile' => [
            'label' => 'Sicheres Profil erzwingen',
            'helper' => 'Spieler benötigen einen von Mojang signierten Schlüssel für Chatnachrichten.',
        ],
        'allow-flight' => [
            'label' => 'Fliegen erlauben',
            'helper' => 'Spieler im Überlebensmodus nicht fürs Fliegen kicken, wird von manchen Mods und Plugins benötigt.',
        ],
        'hide-online-players' => [
            'label' => 'Online-Spieler verbergen',
            'helper' => 'Die Liste der Online-Spieler nicht in der Serverliste anzeigen.',
        ],
        'player-idle-timeout' => [
            'label' => 'Inaktivitäts-Timeout',
            'helper' => 'Spieler kicken, die so lange inaktiv sind. 0 deaktiviert es.',
            'suffix' => 'Minuten',
        ],
        'spawn-protection' => [
            'label' => 'Spawnschutz-Radius',
            'helper' => 'Nur Operatoren können in diesem Radius um den Spawn bauen. 0 deaktiviert es.',
            'suffix' => 'Blöcke',
        ],
        'op-permission-level' => [
            'label' => 'Operator-Berechtigungsstufe',
            'options' => [
                '1' => '1 - Spawnschutz umgehen',
                '2' => '2 - Cheat-Befehle nutzen',
                '3' => '3 - Spieler verwalten',
                '4' => '4 - Server verwalten',
            ],
        ],
        'level-name' => [
            'label' => 'Weltname',
            'helper' => 'Name des Weltordners. Existiert er nicht, wird eine neue Welt generiert.',
        ],
        'level-seed' => [
            'label' => 'Welt-Seed',
            'helper' => 'Leer lassen für einen zufälligen Seed.',
        ],
        'level-type' => [
            'label' => 'Welttyp',
            'options' => [
                'normal' => 'Normal',
                'flat' => 'Flachland',
                'large_biomes' => 'Große Biome',
                'amplified' => 'Zerklüftet',
                'single_biome_surface' => 'Einzelnes Biom',
            ],
        ],
        'generate-structures' => [
            'label' => 'Bauwerke generieren',
            'helper' => 'Dörfer, Tempel, Festungen und so weiter.',
        ],
        'allow-nether' => [
            'label' => 'Nether erlauben',
        ],
        'spawn-monsters' => [
            'label' => 'Monster spawnen',
        ],
        'spawn-animals' => [
            'label' => 'Tiere spawnen',
        ],
        'spawn-npcs' => [
            'label' => 'Dorfbewohner spawnen',
        ],
        'max-world-size' => [
            'label' => 'Weltbarriere-Radius',
            'suffix' => 'Blöcke',
        ],
        'view-distance' => [
            'label' => 'Sichtweite',
            'helper' => 'Wie weit Spieler sehen können.',
            'suffix' => 'Chunks',
        ],
        'simulation-distance' => [
            'label' => 'Simulationsdistanz',
            'helper' => 'Wie weit um Spieler herum sich Kreaturen bewegen und Pflanzen wachsen.',
            'suffix' => 'Chunks',
        ],
        'entity-broadcast-range-percentage' => [
            'label' => 'Entity-Sichtweite',
            'helper' => 'Wie nah Entities sein müssen, um an Spieler gesendet zu werden.',
            'suffix' => '%',
        ],
        'pause-when-empty-seconds' => [
            'label' => 'Pausieren wenn leer',
            'helper' => 'Den Server pausieren, wenn so lange kein Spieler online war. 0 oder weniger deaktiviert es.',
            'suffix' => 'Sekunden',
        ],
        'max-tick-time' => [
            'label' => 'Watchdog-Timeout',
            'helper' => 'Den Server stoppen, wenn ein einzelner Tick länger dauert. -1 deaktiviert es.',
            'suffix' => 'ms',
        ],
        'network-compression-threshold' => [
            'label' => 'Netzwerk-Kompressionsschwelle',
            'helper' => 'Pakete größer als dieser Wert komprimieren. -1 deaktiviert die Kompression.',
            'suffix' => 'Bytes',
        ],
        'sync-chunk-writes' => [
            'label' => 'Synchrones Speichern von Chunks',
            'helper' => 'Sicherer gegen beschädigte Welten, aber langsamer.',
        ],
        'resource-pack' => [
            'label' => 'Ressourcenpaket-URL',
            'helper' => 'Ein direkter Download-Link zu einer .zip-Datei.',
        ],
        'resource-pack-sha1' => [
            'label' => 'Ressourcenpaket-SHA-1',
            'helper' => 'Damit können Clients den Download prüfen und zwischenspeichern.',
        ],
        'resource-pack-prompt' => [
            'label' => 'Ressourcenpaket-Hinweis',
            'helper' => 'Eigene Nachricht, wenn Spieler zum Herunterladen aufgefordert werden.',
        ],
        'require-resource-pack' => [
            'label' => 'Ressourcenpaket erforderlich',
            'helper' => 'Spieler kicken, die das Ressourcenpaket ablehnen.',
        ],
        'enable-command-block' => [
            'label' => 'Befehlsblöcke',
        ],
        'function-permission-level' => [
            'label' => 'Funktions-Berechtigungsstufe',
            'options' => [
                '1' => '1 - Spawnschutz umgehen',
                '2' => '2 - Cheat-Befehle nutzen',
                '3' => '3 - Spieler verwalten',
                '4' => '4 - Server verwalten',
            ],
        ],
        'broadcast-console-to-ops' => [
            'label' => 'Konsole an Operatoren senden',
        ],
        'enable-status' => [
            'label' => 'In Serverliste anzeigen',
            'helper' => 'Deaktiviert erscheint der Server offline, Spieler können aber trotzdem beitreten.',
        ],
        'accepts-transfers' => [
            'label' => 'Transfers akzeptieren',
            'helper' => 'Spieler dürfen von anderen Servern hierher übertragen werden.',
        ],
        'log-ips' => [
            'label' => 'IP-Adressen protokollieren',
        ],
    ],

    'motd' => [
        'toolbar' => [
            'label' => 'Formatierung',
            'undo' => 'Rückgängig',
            'redo' => 'Wiederholen',
            'color' => 'Textfarbe',
            'default_color' => 'Standard (Grau)',
            'symbols' => 'Sonderzeichen einfügen',
            'source' => 'Quelltext mit §-Codes bearbeiten',
        ],
        'symbols' => [
            'arrows' => 'Pfeile',
            'corner_arrows' => 'Eck-Pfeile',
            'lines' => 'Linien & Rahmen',
            'symbols' => 'Symbole',
        ],
        'line_limit' => 'Die Serverliste zeigt maximal zwei Zeilen.',
        'codes' => [
            '0' => 'Schwarz',
            '1' => 'Dunkelblau',
            '2' => 'Dunkelgrün',
            '3' => 'Dunkeltürkis',
            '4' => 'Dunkelrot',
            '5' => 'Dunkellila',
            '6' => 'Gold',
            '7' => 'Grau',
            '8' => 'Dunkelgrau',
            '9' => 'Blau',
            'a' => 'Grün',
            'b' => 'Türkis',
            'c' => 'Rot',
            'd' => 'Helllila',
            'e' => 'Gelb',
            'f' => 'Weiß',
            'k' => 'Verschleiert',
            'l' => 'Fett',
            'm' => 'Durchgestrichen',
            'n' => 'Unterstrichen',
            'o' => 'Kursiv',
            'r' => 'Zurücksetzen',
        ],
    ],

    'callouts' => [
        'missing_title' => 'Keine server.properties gefunden',
        'missing_body' => 'Die Datei wird beim ersten Start des Servers erstellt. Starte deinen Server einmal und komm dann zurück.',
        'not_populated_title' => 'server.properties ist noch nicht vollständig',
        'not_populated_body' => 'Minecraft schreibt alle Einstellungen beim ersten Start des Servers in diese Datei. Starte deinen Server (neu) und lade diese Seite neu, um sie zu bearbeiten. Bis dahin werden nur die Einstellungen angezeigt, die bereits in der Datei stehen.',
        'load_failed_title' => 'server.properties konnte nicht geladen werden',
        'load_failed_body' => 'Die Node ist nicht erreichbar. Bitte versuche es später erneut.',
        'restart_title' => 'Dein Server läuft',
        'restart_body' => 'Minecraft liest diese Einstellungen nur beim Start, Änderungen werden nach einem Neustart wirksam.',
    ],

    'hints' => [
        'managed_by_egg' => 'Wird vom Panel verwaltet und bei jedem Serverstart gesetzt.',
        'locked' => 'Von einem Administrator gesperrt.',
        'new_world_only' => 'Gilt nur, wenn eine neue Welt generiert wird.',
    ],

    'actions' => [
        'edit_file' => 'server.properties manuell bearbeiten',
        'save' => 'Speichern',
        'save_and_restart' => 'Speichern & Neustarten',
    ],

    'modals' => [
        'restart_description' => 'Deine Änderungen werden gespeichert und der Server neu gestartet. Spieler, die online sind, werden getrennt.',
    ],

    'notifications' => [
        'saved' => 'Servereinstellungen gespeichert',
        'saved_body' => 'Geändert: :properties',
        'no_changes' => 'Nichts zu speichern',
        'save_failed' => 'Speichern fehlgeschlagen',
        'save_failed_body' => 'Die server.properties konnte nicht geschrieben werden. Bitte versuche es erneut oder kontaktiere den Support, wenn das Problem weiterhin besteht.',
        'restarting' => 'Server wird neu gestartet',
        'restart_failed' => 'Neustart fehlgeschlagen',
        'restart_failed_body' => 'Deine Änderungen wurden gespeichert, aber der Server konnte nicht neu gestartet werden.',
    ],

    'settings' => [
        'locked_properties' => 'Gesperrte Einstellungen',
        'locked_properties_helper' => 'Benutzer sehen diese Einstellungen, können sie aber nicht ändern, z. B. max-players.',
        'saved' => 'Einstellungen gespeichert',
    ],
];
