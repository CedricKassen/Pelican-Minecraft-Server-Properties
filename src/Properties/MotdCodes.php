<?php

namespace CedricKassen\MinecraftServerProperties\Properties;

/**
 * The § color codes a motd can contain, with the colors minecraft renders them in.
 */
class MotdCodes
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

    // The server list draws uncolored motd text in this gray.
    public const DEFAULT_COLOR = '#808080';

    /**
     * Symbols offered by the editor, grouped for the picker. Only plain text symbols that minecraft's font draws
     * as monochrome glyphs, nothing that is usually shown as a colored emoji.
     */
    public const SYMBOLS = [
        'arrows' => ['→', '←', '↑', '↓', '↔', '↕', '⇒', '⇐', '➜', '➔', '➤', '►', '◄', '▲', '▼', '»', '«', '›', '‹'],
        'corner_arrows' => ['↳', '↲', '↱', '↰', '↴', '↵', '⤷', '⤶', '⤴', '⤵', '↪', '↩', '➥', '➦', '↺', '↻'],
        'lines' => ['└', '┘', '┌', '┐', '╰', '╯', '╭', '╮', '├', '┤', '┬', '┴', '│', '─', '┃', '━', '║', '═', '▌', '▐', '░', '▒', '▓', '█'],
        'symbols' => ['•', '●', '○', '■', '□', '◆', '◇', '★', '☆', '✦', '✧', '♥', '♦', '♣', '♠', '✔', '✘', '✚', '∞', '♪', '♫', '⚔', '☀', '❄', '☠', '✎', '✉', '☺', '©', '®', '™'],
    ];
}
