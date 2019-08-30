<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

class MemoryClient implements CacheInterface
{
    use MultipleKeysTrait;

    protected $cache   = [];
    protected $expires = [];

    /**
     * Fetches a value from the cache.
     *
     * @param string $key     The unique key of this item in the cache.
     * @param mixed  $default Default value to return if the key does not exist.
     *
     * @return mixed The value of the item from the cache, or $default in case of cache miss.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function get($key, $default = null)
    {
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Provided key must be a string');
        }

        $expiration = $this->expires[$key] ?? null;

        if ($expiration && time() > $expiration) {
            unset($this->cache[$key], $this->expires[$key]);

            return $default;
        }

        return $this->cache[$key] ?? $default;
    }

    /**
     * Persists data in the cache, uniquely referenced by a key with an optional expiration TTL time.
     *
     * @param string                 $key   The key of the item to store.
     * @param mixed                  $value The value of the item to store, must be serializable.
     * @param null|int|\DateInterval $ttl   Optional. The TTL value of this item. If no value is sent and
     *                                      the driver supports TTL then the library may set a default value
     *                                      for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function set($key, $value, $ttl = null)
    {
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Provided key must be a string');
        }

        $this->cache[$key] = $value;

        unset($this->expires[$key]);

        if ($ttl) {
            $this->expires[$key] = time() + $ttl;
        }

        return true;
    }

    /**
     * Delete an item from the cache by its unique key.
     *
     * @param string $key The unique cache key of the item to delete.
     *
     * @return bool True if the item was successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function delete($key)
    {
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Provided key must be a string');
        }

        unset($this->cache[$key], $this->expires[$key]);

        return true;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        $this->cache   = [];
        $this->expires = [];

        return true;
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
        if (!\is_string($key)) {
            throw new InvalidArgumentException('Provided key must be a string');
        }

        $expiration = $this->expires[$key] ?? null;

        if ($expiration && time() > $expiration) {
            unset($this->cache[$key], $this->expires[$key]);

            return false;
        }

        return array_key_exists($key, $this->cache);
    }
}