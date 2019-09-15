<?php

namespace Beryllium\Cache\Client;

use Psr\SimpleCache\CacheInterface;

class CascadeClient implements CacheInterface
{
    /** @var CacheInterface[] An ordered list of all cascading clients */
    protected $clients = [];

    /**
     * @var bool determine whether the get() method should backfill all cascades on a cache hit
     */
    protected $backfill;

    public function __construct(CacheInterface ...$clients)
    {
        $this->clients = $clients;
    }

    public function addClient(CacheInterface $client): CascadeClient
    {
        $this->clients[] = $client;

        return $this;
    }

    public function get($key, $default = null)
    {
        foreach ($this->clients as $client) {
            $result = $client->get($key);

            if (!$result) {
                continue;
            }

            if ($this->backfill) {
                $this->set($key, $result);
            }

            return $result;
        }

        return $default;
    }

    public function set($key, $value, $ttl = null)
    {
        $result = false;
        foreach ($this->clients as $client) {
            $result = $client->set($key, $value, $ttl) || $result;
        }

        return $result;
    }

    public function delete($key)
    {
        $result = false;
        foreach ($this->clients as $client) {
            $result = $client->delete($key) || $result;
        }

        return $result;
    }

    public function clear()
    {
        $result = false;
        foreach ($this->clients as $client) {
            $result = $client->clear() || $result;
        }

        return $result;
    }

    public function getMultiple($keys, $default = null)
    {
        $result = [];
        foreach ($this->clients as $client) {
            $result += array_filter((array)$client->getMultiple($keys));
        }

        return $result;
    }

    public function setMultiple($values, $ttl = null)
    {
        $result = false;
        foreach ($this->clients as $client) {
            $result = $client->setMultiple($values, $ttl) || $result;
        }

        return $result;
    }

    public function deleteMultiple($keys)
    {
        $result = false;
        foreach ($this->clients as $client) {
            $result = $client->deleteMultiple($keys) || $result;
        }

        return $result;
    }

    public function has($key)
    {
        foreach ($this->clients as $client) {
            if ($client->has($key)) {
                return true;
            }
        }

        return false;
    }

    public function enableBackfill(): CascadeClient
    {
        $this->backfill = true;

        return $this;
    }

    public function disableBackfill(): CascadeClient
    {
        $this->backfill = false;

        return $this;
    }
}