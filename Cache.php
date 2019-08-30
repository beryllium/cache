<?php

namespace Beryllium\Cache;

use Psr\SimpleCache\CacheInterface;
use Psr\SimpleCache\InvalidArgumentException;

/**
 * Base cache implementation that standardizes calls to cache clients
 *
 * @uses CacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class Cache implements CacheInterface
{
    public const DEFAULT_TTL    = 300;
    public const DEFAULT_PREFIX = '';

    /** @var CacheInterface $client */
    private $client;

    private $ttl;
    private $prefix;

    /**
     * @param CacheInterface $client
     * @param string|null    $prefix
     * @param int|null       $ttl
     */
    public function __construct(CacheInterface $client, string $prefix = null, int $ttl = null)
    {
        $this->client = $client;
        $this->prefix = $prefix ?? static::DEFAULT_PREFIX;
        $this->ttl    = $ttl    ?? static::DEFAULT_TTL;
    }

    /**
     * Retrieve a value from the cache using the provided key
     *
     * @param string|array $key         The unique key or array of keys identifying the data to be retrieved.
     * @param null         $default     Default value to return on cache miss
     *
     * @return mixed The requested data, or false if there is an error
     * @throws InvalidArgumentException
     */
    public function get($key, $default = null)
    {
        if (\is_array($key)) {
            return $this->getMultiple($key, $default);
        }

        return $this->client->get($this->buildKey($key), $default);
    }

    /**
     * Add a key/value to the cache
     *
     * @param string $key   A unique key to identify the data you want to store
     * @param mixed  $value The value you want to store in the cache
     * @param int    $ttl   Optional: Lifetime of the data
     *
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function set($key, $value, $ttl = null): bool
    {
        $ttl = $ttl ?? $this->ttl;

        return $this->client->set($this->buildKey($key), $value, $ttl);
    }

    /**
     * Delete a key from the cache
     *
     * @param string $key Unique key
     *
     * @return boolean
     * @throws InvalidArgumentException
     */
    public function delete($key)
    {
        return $this->client->delete($this->buildKey($key));
    }

    /**
     * Change the default lifetime of the data (default: 300 seconds - five minutes)
     *
     * @param int $ttl
     * @return void
     */
    public function setTtl(int $ttl): void
    {
        $this->ttl = $ttl;
    }

    /**
     * @return CacheInterface
     */
    public function getClient(): CacheInterface
    {
        return $this->client;
    }

    /**
     * @param string $prefix    A string to use as the prefix for all keys
     */
    public function setPrefix(string $prefix): void
    {
        $this->prefix = $prefix;
    }

    /**
     * Build the key with its prefix to send to the client
     *
     * @param $key
     * @return string
     */
    private function buildKey($key): string
    {
        return $this->prefix . $key;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->client->clear();
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable     A list of key => value pairs.
     *                      Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        $keys = array_map([$this, 'buildKey'], (array)$keys);

        return $this->client->getMultiple($keys, $default);
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
     * @throws InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null): bool
    {
        $prefixedValues = [];

        foreach ($values as $key => $value) {
            $prefixedValues[$key] = $value;
        }

        return $this->client->setMultiple($prefixedValues, $ttl);
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys): bool
    {
        $keys = array_map([$this, 'buildKey'], (array)$keys);

        return $this->client->deleteMultiple($keys);
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
     * @throws InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key): bool
    {
        $key = $this->buildKey($key);

        return $this->client->has($key);
    }
}