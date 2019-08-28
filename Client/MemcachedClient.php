<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Client\ServerVerifier\MemcacheServerVerifier;
use Beryllium\Cache\Client\ServerVerifier\ServerVerifierInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Client interface for Memcache servers
 *
 * @uses CacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class MemcachedClient implements CacheInterface
{
    /** @var \Memcached|null Memcache instance */
    protected $memcache;

    protected $safe    = false;
    protected $servers = [];

    /**
     * Constructs the cache client using an injected Memcache instance
     *
     * @access public
     */
    public function __construct(\Memcached $memcache = null, ServerVerifierInterface $serverVerifier = null)
    {
        $this->memcache = $memcache ?: new \Memcached();
        $this->serverVerifier = $serverVerifier ?: new MemcacheServerVerifier();
    }

    /**
     * Retrieve a value from memcache
     *
     * @param string|array $key Unique identifier or array of identifiers
     * @return mixed Requested value, or false if an error occurs
     */
    public function get($key, $default = null)
    {
        if ($this->safe) {
            return $this->memcache->get($key) ?? $default;
        }

        return false;
    }

    /**
     * Add a value to the memcache
     *
     * @param string $key Unique key
     * @param mixed $value A value. I recommend a string, be it serialized or not - other values haven't been tested :)
     * @param int $ttl Number of seconds for the value to be valid for
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        if ($this->safe) {
            return $this->memcache->set($key, $value, false, $ttl);
        }

        return false;
    }

    /**
     * Delete a value from the memcache
     *
     * @param string $key Unique key
     * @return boolean
     */
    public function delete($key)
    {
        if ($this->safe) {
            return $this->memcache->delete($key, 0);
        }

        return false;
    }

    /**
     * Get the current Memcache object. Can be useful for retrieving the service after using the addServer method.
     *
     * @return \Memcached|null
     */
    public function getMemcached()
    {
        return $this->memcache;
    }

    /**
     * Add a server to the memcache pool
     *
     * @param string $ip Location of memcache server
     * @param int $port Optional: Port number (default: 11211)
     * @return boolean
     */
    public function addServer($ip = '127.0.0.1', $port = 11211)
    {
        if (!$this->serverVerifier->verify($ip, $port)) {
            return false;
        }

        if ($status = $this->memcache->addServer($ip, $port)) {
            $this->safe = true;
        }

        return $status;
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        // TODO: Implement clear() method.
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
        // TODO: Implement getMultiple() method.
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
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
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
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
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
        // TODO: Implement has() method.
    }
}