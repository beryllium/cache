<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Client\ServerVerifier\ServerVerifierInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Client interface for Memcache servers
 *
 * A note from the PHP.net documentation about expiration time/TTL values:
 *
 * Some storage commands involve sending an expiration value (relative to an item or to an operation requested by the
 * client) to the server. In all such cases, the actual value sent may either be Unix time (number of seconds since
 * January 1, 1970, as an integer), or a number of seconds starting from current time. In the latter case, this number
 * of seconds may not exceed 60*60*24*30 (number of seconds in 30 days); if the expiration value is larger than that,
 * the server will consider it to be real Unix time value rather than an offset from current time.
 *
 * If the expiration value is 0 (the default), the item never expires (although it may be deleted from the server to
 * make place for other items).
 *
 * @uses    CacheInterface
 * @package
 * @version $id$
 * @author  Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class MemcachedClient implements CacheInterface
{
    use MultipleKeysTrait;

    /** @var \Memcached|null Memcached instance */
    protected $memcache;

    /** @var ServerVerifierInterface|null */
    protected $serverVerifier;

    /**
     * Constructs the cache client using an injected Memcache instance
     *
     * @access public
     *
     * @param \Memcached|null              $memcache
     * @param ServerVerifierInterface|null $serverVerifier
     */
    public function __construct(?\Memcached $memcache = null, ?ServerVerifierInterface $serverVerifier = null)
    {
        $this->memcache       = $memcache ?: new \Memcached();
        $this->serverVerifier = $serverVerifier;
    }

    /**
     * Retrieve a value from memcache
     *
     * @param string|array $key     Unique identifier or array of identifiers
     * @param mixed        $default Default value in case key is not found
     *
     * @return mixed Requested value, or default if an error occurs or the key is not found
     */
    public function get($key, $default = null)
    {
        $result = $this->memcache->get($key);

        if (!$result && \Memcached::RES_NOTFOUND === $this->memcache->getResultCode()) {
            return $default;
        }

        return $result;
    }

    /**
     * Add a value to the memcache
     *
     * @param string $key   Unique key
     * @param mixed  $value A value to cache.
     * @param int    $ttl   Number of seconds for the value to be valid for.
     *
     * @return  boolean
     */
    public function set($key, $value, $ttl = null)
    {
        return $this->memcache->set($key, $value, $ttl);
    }

    /**
     * Delete a value from the memcache
     *
     * @param string $key Unique key
     *
     * @return boolean
     */
    public function delete($key)
    {
        return $this->memcache->delete($key);
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
     * @param string $ip   Location of memcache server
     * @param int    $port Optional: Port number (default: 11211)
     *
     * @return boolean
     */
    public function addServer($ip = '127.0.0.1', $port = 11211)
    {
        if ($this->serverVerifier && !$this->serverVerifier->verify($ip, $port)) {
            return false;
        }

        return $this->memcache->addServer($ip, $port);
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        return $this->memcache->flush();
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
        // Very wasteful. Definitely don't rely on this.
        $this->get($key);

        return !(\Memcached::RES_NOTFOUND === $this->memcache->getResultCode());
    }
}