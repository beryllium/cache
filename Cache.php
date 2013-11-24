<?php

namespace Beryllium\Cache;

use Beryllium\Cache\Client\ClientInterface;

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
    const DEFAULT_TTL = 300;

    /** @var CacheInterface $client */
    private $client;
    private $ttl = self::DEFAULT_TTL;
    private $prefix = '';

    /**
     * @param ClientInterface $client
     */
    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    /**
     * Retrieve a value from the cache using the provided key
     *
     * @param string|array $key The unique key or array of keys identifying the data to be retrieved.
     * @return mixed The requested data, or false if there is an error
     */
    public function get($key)
    {
        return $this->client->get($this->getKey($key));
    }

    /**
     * Add a key/value to the cache
     *
     * @param string $key A unique key to identify the data you want to store
     * @param mixed $value The value you want to store in the cache
     * @param int $ttl Optional: Lifetime of the data
     * @return boolean
     */
    public function set($key, $value, $ttl = null)
    {
        $ttl = is_null($ttl) ? $this->ttl : $ttl;

        return $this->client->set($this->getKey($key), $value, $ttl);
    }

    /**
     * Delete a key from the cache
     *
     * @param string $key Unique key
     * @return boolean
     */
    public function delete($key)
    {
        return $this->client->delete($this->getKey($key));
    }

    /**
     * Change the default lifetime of the data (default: 300 seconds - five minutes)
     *
     * @param int $ttl
     * @return void
     */
    public function setTtl($ttl)
    {
        $this->ttl = $ttl;
    }

    /**
     * @return CacheInterface
     */
    public function getClient()
    {
        return $this->client;
    }

    /**
     * @param string $prefix
     */
    public function setPrefix($prefix)
    {
        $this->prefix = $prefix;
    }

    /**
     * Build the key with its prefix to send to the client
     *
     * @param $key
     * @return string
     */
    private function getKey($key)
    {
        return $this->prefix . $key;
    }
}