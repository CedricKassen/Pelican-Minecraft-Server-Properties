<?php

namespace CedricKassen\MinecraftServerProperties\Properties;

/**
 * Renders a motd with its § formatting codes to HTML, roughly like the server list shows it.
 */
class MotdRenderer
{
    public const COLORS = [
        '0' => '#000000',
        '1' => '#0000AA',
        '2' => '#00AA00',
        '3' => '#00AAAA',
        '4' => '#AA0000',
        '5' => '#AA00AA',
        '6' => '#FFAA00',
        '7' => '#AAAAAA',
        '8' => '#555555',
        '9' => '#5555FF',
        'a' => '#55FF55',
        'b' => '#55FFFF',
        'c' => '#FF5555',
        'd' => '#FF55FF',
        'e' => '#FFFF55',
        'f' => '#FFFFFF',
    ];

    public const FORMATS = [
        'l' => 'font-weight: bold',
        'm' => 'text-decoration: line-through',
        'n' => 'text-decoration: underline',
        'o' => 'font-style: italic',
        // obfuscated text, it would flicker in game
        'k' => 'filter: blur(3px)',
    ];

    public static function toHtml(?string $motd): string
    {
        // The server list only shows the first two lines.
        $lines = array_slice(explode("\n", str_replace(["\r\n", "\r"], "\n", $motd ?? '')), 0, 2);

        $html = '';

        foreach ($lines as $line) {
            $html .= '<div style="min-height: 1.25em; white-space: pre;">' . self::renderLine($line) . '</div>';
        }

        return '<div style="background: #1e1e1e; color: ' . self::COLORS['7'] . '; font-family: ui-monospace, monospace; padding: 0.5rem 0.75rem; border-radius: 0.5rem; overflow-x: auto;">' . $html . '</div>';
    }

    private static function renderLine(string $line): string
    {
        $parts = preg_split('/§([0-9a-fk-or])/iu', $line, -1, PREG_SPLIT_DELIM_CAPTURE);
        if ($parts === false) {
            return e($line);
        }

        $color = null;
        $formats = [];
        $html = '';

        foreach ($parts as $index => $part) {
            // Odd indexes are the captured codes, even ones the text in between.
            if ($index % 2 === 1) {
                $code = strtolower($part);

                if (isset(self::COLORS[$code])) {
                    // Like in game a color resets the formatting.
                    $color = self::COLORS[$code];
                    $formats = [];
                } elseif ($code === 'r') {
                    $color = null;
                    $formats = [];
                } else {
                    $formats[$code] = self::FORMATS[$code];
                }

                continue;
            }

            if ($part === '') {
                continue;
            }

            $style = $color ? "color: $color;" : '';
            $active = $formats;
            if (isset($active['m'], $active['n'])) {
                unset($active['m'], $active['n']);
                $style .= 'text-decoration: underline line-through;';
            }
            $style .= implode(';', $active);

            $html .= $style === '' ? e($part) : '<span style="' . $style . '">' . e($part) . '</span>';
        }

        return $html;
    }
}
