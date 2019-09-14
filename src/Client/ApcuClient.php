<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Exception\InvalidArgumentException;
use Psr\SimpleCache\CacheInterface;

/**
 * APCu Client
 */
class ApcuClient implements CacheInterface
{
    public function __construct()
    {
    }

    /**
     * Retrieve the value corresponding to a provided key
     *
     * @param string $key       Unique identifier
     * @param null   $default   Default value to return on a cache miss
     *
     * @return  mixed           Result from the cache
     */
    public function get($key, $default = null)
    {
        return apcu_fetch($key) ?? $default;
    }

    /**
     * Add a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @param mixed $value Data to store in the cache
     * @param int $ttl Lifetime for stored data (in seconds)
     * @return boolean
     */
    public function set($key, $value, $ttl = null): bool
    {
        return apcu_store($key, $value, $ttl ?? 0);
    }

    /**
     * Delete a value from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key): bool
    {
        return apcu_delete($key);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear(): bool
    {
        return apcu_clear_cache();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        if (!\is_array($keys) || !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Unable to getMultiple using non-array/non-Traversable keys');
        }

        return apcu_fetch($keys);
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null): bool
    {
        if (!\is_array($values) || !$values instanceof \Traversable) {
            throw new InvalidArgumentException('Unable to setMultiple using non-array/non-Traversable values');
        }

        return apcu_store($values, null, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys): bool
    {
        if (!\is_array($keys) || !$keys instanceof \Traversable) {
            throw new InvalidArgumentException('Unable to deleteMultiple using non-array/non-Traversable keys');
        }

        return apcu_delete($keys);
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
    public function has($key): bool
    {
        return apcu_exists($key);
    }
}