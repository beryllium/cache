<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Client\ServerVerifier\MemcacheServerVerifier;
use Beryllium\Cache\Client\ServerVerifier\ServerVerifierInterface;

/**
 * Client interface for Memcache servers
 *
 * @uses CacheInterface
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class MemcacheClient implements ClientInterface
{
    /** @var \Memcache|null Memcache instance */
    protected $memcache;

    protected $safe = false;
    protected $servers = array();

    /**
     * Constructs the cache client using an injected Memcache instance
     *
     * @access public
     */
    public function __construct(\Memcache $memcache = null, ServerVerifierInterface $serverVerifier = null)
    {
        $this->memcache = $memcache ?: new \Memcache();
        $this->serverVerifier = $serverVerifier ?: new MemcacheServerVerifier();
    }

    /**
     * Retrieve a value from memcache
     *
     * @param string|array $key Unique identifier or array of identifiers
     * @return mixed Requested value, or false if an error occurs
     */
    public function get($key)
    {
        if ($this->safe) {
            return $this->memcache->get($key);
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
    public function set($key, $value, $ttl)
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
     * @return \Memcache|null
     */
    public function getMemcache()
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
}