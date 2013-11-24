<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Statistics;

/**
 * APC Client
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class APCClient implements ClientInterface
{
    private $safe;

    public function __construct()
    {
        $this->safe = extension_loaded('apc');
    }

    /**
     * Retrieve the value corresponding to a provided key
     *
     * @param string $key Unique identifier
     * @return mixed Result from the cache
     */
    public function get($key)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_fetch($key);
    }

    /**
     * Add a value to the cache under a unique key
     *
     * @param string $key Unique key to identify the data
     * @param mixed $value Data to store in the cache
     * @param int $ttl Lifetime for stored data (in seconds)
     * @return boolean
     */
    public function set($key, $value, $ttl)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_store($key, $value, $ttl);
    }

    /**
     * Delete a value from the cache
     *
     * @param string $key
     * @return boolean
     */
    public function delete($key)
    {
        if (!$this->safe) {
            return false;
        }

        return apc_delete($key);
    }
}