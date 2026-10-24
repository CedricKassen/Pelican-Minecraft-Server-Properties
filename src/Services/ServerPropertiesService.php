<?php

namespace CedricKassen\MinecraftServerProperties\Services;

use App\Models\Server;
use App\Repositories\Daemon\DaemonFileRepository;
use CedricKassen\MinecraftServerProperties\Properties\PropertiesFile;
use Illuminate\Contracts\Filesystem\FileNotFoundException;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Support\Facades\Cache;

class ServerPropertiesService
{
    public const FILE = 'server.properties';

    // Anything bigger is certainly not a server.properties.
    protected const MAX_FILE_SIZE = 256 * 1024;

    // Keys every minecraft version writes on startup. Before the first start the file often only
    // contains what the egg put there (server-port etc.), or nothing at all.
    public const LANDMARK_KEYS = [
        'motd',
        'level-name',
        'max-players',
        'online-mode',
        'difficulty',
        'gamemode',
        'view-distance',
        'white-list',
    ];

    // A few landmarks may be added by hand, so a file only counts as written by minecraft with most of them.
    protected const MIN_LANDMARK_KEYS = 5;

    public static function isPopulated(PropertiesFile $file): bool
    {
        return count(array_intersect_key($file->all(), array_flip(self::LANDMARK_KEYS))) >= self::MIN_LANDMARK_KEYS;
    }

    public static function isEnabledForServer(Server $server): bool
    {
        $server->loadMissing('egg');

        $features = $server->egg->features ?? [];
        $tags = $server->egg->tags ?? [];

        if (in_array('server_properties', $features)) {
            return true;
        }

        // Proxies like velocity or bungeecord have no server.properties.
        return in_array('minecraft', $tags) && !in_array('proxy', $tags);
    }

    /**
     * Keys the egg writes into server.properties on every start (e.g. server-port), changes to
     * them would be overwritten anyway.
     *
     * @return array<int, string>
     */
    public static function getEggManagedKeys(Server $server): array
    {
        $server->loadMissing('egg');

        $configFiles = json_decode($server->egg->inherit_config_files ?? '', true);

        $find = $configFiles[self::FILE]['find'] ?? null;

        return is_array($find) ? array_map('strval', array_keys($find)) : [];
    }

    /** @return array<int, string> */
    public static function getLockedKeys(): array
    {
        return array_values(array_filter((array) config('minecraft-server-properties.locked_properties', [])));
    }

    /**
     * @return PropertiesFile|null null if the server has no server.properties (yet)
     *
     * @throws ConnectionException
     * @throws RequestException
     */
    public function read(Server $server): ?PropertiesFile
    {
        try {
            $content = app(DaemonFileRepository::class)->setServer($server)->getContent(self::FILE, self::MAX_FILE_SIZE);
        } catch (FileNotFoundException) {
            return null;
        }

        return PropertiesFile::parse($content);
    }

    /**
     * Applies the given values to the current file on the server. The file is read again right
     * before writing, so changes made in the meantime (e.g. by the server on startup) to other
     * properties are kept.
     *
     * @param  array<string, string>  $values
     *
     * @throws FileNotFoundException if the file was deleted in the meantime
     * @throws ConnectionException
     * @throws RequestException
     */
    public function write(Server $server, array $values): void
    {
        Cache::lock("minecraft-server-properties:$server->id", 10)->block(5, function () use ($server, $values) {
            $file = $this->read($server) ?? throw new FileNotFoundException(self::FILE);

            foreach ($values as $key => $value) {
                $file->set($key, $value);
            }

            app(DaemonFileRepository::class)->setServer($server)->putContent(self::FILE, $file->toString())->throw();
        });
    }
}
