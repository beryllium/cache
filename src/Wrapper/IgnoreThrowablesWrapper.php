<?php

namespace Beryllium\Cache\Wrapper;

use Psr\SimpleCache\CacheInterface;

class IgnoreThrowablesWrapper implements CacheInterface
{
    protected $client;

    public function __construct(CacheInterface $client)
    {
        $this->client = $client;
    }

    protected function silence(Callable $action, $default = null)
    {
        try {
            return $action();
        } catch (\Throwable $e) {
        }

        return $default;
    }

    public function get($key, $default = null)
    {
        return $this->silence(
            function () use ($key, $default) {
                return $this->client->get($key, $default);
            },
            $default
        );
    }

    public function set($key, $value, $ttl = null)
    {
        return (bool)$this->silence(
            function () use ($key, $value, $ttl) {
                return $this->client->set($key, $value, $ttl);
            }
        );
    }

    public function delete($key)
    {
        return (bool)$this->silence(
            function () use ($key) {
                return $this->client->delete($key);
            }
        );
    }

    public function clear()
    {
        return (bool)$this->silence(
            function () {
                return $this->client->clear();
            }
        );
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->silence(
            function () use ($keys, $default) {
                return $this->client->getMultiple($keys, $default);
            }
        ) ?? [];
    }

    public function setMultiple($values, $ttl = null)
    {
        return (bool)$this->silence(
            function () use ($values, $ttl) {
                return $this->client->setMultiple($values, $ttl);
            }
        );
    }

    public function deleteMultiple($keys)
    {
        return (bool)$this->silence(
            function () use ($keys) {
                return $this->client->deleteMultiple($keys);
            }
        );
    }
    public function has($key)
    {
        return (bool)$this->silence(
            function () use ($key) {
                return $this->client->has($key);
            }
        );
    }
}