<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Exception\InvalidPathException;
use Psr\SimpleCache\CacheInterface;

/**
 * Uses the filesystem to store and retrieve cache entries
 */
class FilecacheClient implements CacheInterface
{
    use MultipleKeysTrait;

    private $path;

    /**
     * @param string $path
     */
    public function __construct(string $path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (empty($path)) {
            throw new InvalidPathException('Path was not provided');
        }

        if (!is_dir($path) && !mkdir($path) && !is_dir($path)) {
            throw new InvalidPathException('Provided path directory does not exist and/or could not be created');
        }

        if (!is_writable($path)) {
            throw new InvalidPathException('Provided path is not a writable directory');
        }

        $this->path = $path;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get($key, $default = null)
    {
        if (empty($key)) {
            return $default;
        }

        if (!file_exists($this->getFilename($key))) {
            return $default;
        }

        $file = json_decode(file_get_contents($this->getFilename($key)), true);

        if (!is_array($file) || $file['key'] !== $key) {
            return $default;
        }

        if ($file['ttl'] != 0 && time() - $file['ctime'] > $file['ttl']) {
            $this->delete($key);

            return $default;
        }

        return $this->unserialize($file['value']) ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
    {
        if (empty($key)) {
            return false;
        }

        $file = array(
            'key'   => $key,
            'value' => $this->serialize($value),
            'ttl'   => $ttl,
            'ctime' => time(),
        );

        return (bool)file_put_contents($this->getFilename($key), json_encode($file));
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            unlink($filename);

            return true;
        }

        return false;
    }

    /**
     * Build a full path for the provided key
     *
     * @param string $key
     * @return string
     */
    protected function getFilename(string $key): string
    {
        return $this->path . DIRECTORY_SEPARATOR . md5($key) . '_file.cache';
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        throw new \RuntimeException('FilecacheClient clear() support is not implemented.');
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        if (empty($key)) {
            return false;
        }

        return file_exists($this->getFilename($key));
    }

    protected function serialize($data)
    {
        return serialize($data);
    }

    protected function unserialize($data, ?$options = null)
    {
        return unserialize($data, $options);
    }
}
