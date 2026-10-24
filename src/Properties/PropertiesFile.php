<?php

namespace CedricKassen\MinecraftServerProperties\Properties;

/**
 * A .properties file as minecraft reads it (java.util.Properties).
 *
 * Changing a value only rewrites the line of that entry, so comments, the order of the
 * entries and everything we don't know about stays exactly as it was.
 */
class PropertiesFile
{
    /**
     * @param  array<int, array{raw: string, key?: string, value?: string, prefix?: string}>  $entries  one per logical line
     */
    private function __construct(
        private array $entries,
        private readonly string $lineEnding,
        private readonly bool $endsWithLineEnding,
    ) {}

    public static function parse(string $content): self
    {
        $lineEnding = str_contains($content, "\r\n") ? "\r\n" : "\n";
        $endsWithLineEnding = $content !== '' && str_ends_with($content, "\n");

        $lines = preg_split('/\r\n|\r|\n/', (string) preg_replace('/(\r\n|\r|\n)$/', '', $content));
        if ($lines === false || $content === '') {
            $lines = [];
        }

        $entries = [];
        $count = count($lines);

        for ($i = 0; $i < $count; $i++) {
            $raw = $lines[$i];
            $logical = ltrim($raw, " \t\f");

            if ($logical === '' || $logical[0] === '#' || $logical[0] === '!') {
                $entries[] = ['raw' => $raw];

                continue;
            }

            // A line ending in an odd number of backslashes continues on the next one.
            while (self::continuesOnNextLine($logical) && $i + 1 < $count) {
                $i++;
                $raw .= $lineEnding . $lines[$i];
                $logical = substr($logical, 0, -1) . ltrim($lines[$i], " \t\f");
            }

            [$rawKey, $separatorEnd] = self::splitKey($logical);

            $entries[] = [
                'raw' => $raw,
                'key' => self::unescape($rawKey),
                'value' => self::unescape(substr($logical, $separatorEnd)),
                // Keeps the key exactly as written (indentation, spacing around "=") for rewrites.
                'prefix' => substr($raw, 0, strlen($raw) - strlen(ltrim($raw, " \t\f"))) . substr($logical, 0, $separatorEnd),
            ];
        }

        return new self($entries, $lineEnding, $endsWithLineEnding);
    }

    /** @return array<string, string> */
    public function all(): array
    {
        $values = [];

        foreach ($this->entries as $entry) {
            if (isset($entry['key'])) {
                // Like java.util.Properties, the last occurrence of a key wins.
                $values[$entry['key']] = $entry['value'] ?? '';
            }
        }

        return $values;
    }

    public function has(string $key): bool
    {
        return array_key_exists($key, $this->all());
    }

    public function get(string $key): ?string
    {
        return $this->all()[$key] ?? null;
    }

    public function set(string $key, string $value): void
    {
        $index = null;
        foreach ($this->entries as $i => $entry) {
            if (($entry['key'] ?? null) === $key) {
                $index = $i;
            }
        }

        if ($index === null) {
            $prefix = self::escape($key, true) . '=';
            $this->entries[] = ['raw' => $prefix . self::escape($value), 'key' => $key, 'value' => $value, 'prefix' => $prefix];

            return;
        }

        $prefix = $this->entries[$index]['prefix'] ?? (self::escape($key, true) . '=');

        $this->entries[$index] = ['raw' => $prefix . self::escape($value), 'key' => $key, 'value' => $value, 'prefix' => $prefix];
    }

    public function toString(): string
    {
        $content = implode($this->lineEnding, array_column($this->entries, 'raw'));

        return $this->endsWithLineEnding || $content === '' ? $content . $this->lineEnding : $content;
    }

    private static function continuesOnNextLine(string $line): bool
    {
        $backslashes = strlen($line) - strlen(rtrim($line, '\\'));

        return $backslashes % 2 === 1;
    }

    /**
     * @return array{0: string, 1: int} the raw key and the offset at which the value starts
     */
    private static function splitKey(string $line): array
    {
        $length = strlen($line);
        $keyEnd = $length;

        for ($i = 0; $i < $length; $i++) {
            $char = $line[$i];

            if ($char === '\\') {
                $i++;

                continue;
            }

            if ($char === '=' || $char === ':' || $char === ' ' || $char === "\t" || $char === "\f") {
                $keyEnd = $i;

                break;
            }
        }

        $offset = $keyEnd;

        while ($offset < $length && in_array($line[$offset], [' ', "\t", "\f"], true)) {
            $offset++;
        }

        if ($offset < $length && ($line[$offset] === '=' || $line[$offset] === ':')) {
            $offset++;

            while ($offset < $length && in_array($line[$offset], [' ', "\t", "\f"], true)) {
                $offset++;
            }
        }

        return [substr($line, 0, $keyEnd), $offset];
    }

    private static function unescape(string $value): string
    {
        if (!str_contains($value, '\\')) {
            return $value;
        }

        $result = '';
        $length = strlen($value);

        for ($i = 0; $i < $length; $i++) {
            $char = $value[$i];

            if ($char !== '\\' || $i + 1 >= $length) {
                $result .= $char;

                continue;
            }

            $next = $value[++$i];

            if ($next === 'u' && preg_match('/\G[0-9a-fA-F]{4}/', $value, $match, 0, $i + 1)) {
                $codeUnit = hexdec($match[0]);
                $i += 4;

                // Characters outside the BMP are stored as two escaped UTF-16 surrogates.
                if ($codeUnit >= 0xD800 && $codeUnit <= 0xDBFF && preg_match('/\G\\\\u(d[c-f][0-9a-f]{2})/i', $value, $low, 0, $i + 1)) {
                    $codeUnit = 0x10000 + (($codeUnit - 0xD800) << 10) + (hexdec($low[1]) - 0xDC00);
                    $i += 6;
                }

                $result .= mb_chr((int) $codeUnit, 'UTF-8') ?: '';

                continue;
            }

            $result .= match ($next) {
                't' => "\t",
                'n' => "\n",
                'r' => "\r",
                'f' => "\f",
                default => $next,
            };
        }

        return $result;
    }

    /**
     * Escapes like java.util.Properties::store does, non ASCII characters become \uXXXX so the
     * file reads the same no matter which charset the minecraft version assumes.
     */
    private static function escape(string $value, bool $isKey = false): string
    {
        $result = '';

        foreach (mb_str_split($value, 1, 'UTF-8') as $index => $char) {
            $result .= match ($char) {
                '\\' => '\\\\',
                "\t" => '\\t',
                "\n" => '\\n',
                "\r" => '\\r',
                "\f" => '\\f',
                '=', ':', '#', '!' => '\\' . $char,
                ' ' => ($isKey || $index === 0) ? '\\ ' : ' ',
                default => self::escapeCharacter($char),
            };
        }

        return $result;
    }

    private static function escapeCharacter(string $char): string
    {
        $codePoint = mb_ord($char, 'UTF-8');

        if ($codePoint >= 0x20 && $codePoint <= 0x7E) {
            return $char;
        }

        if ($codePoint > 0xFFFF) {
            $codePoint -= 0x10000;

            return sprintf('\\u%04X\\u%04X', 0xD800 + ($codePoint >> 10), 0xDC00 + ($codePoint & 0x3FF));
        }

        return sprintf('\\u%04X', $codePoint);
    }
}
