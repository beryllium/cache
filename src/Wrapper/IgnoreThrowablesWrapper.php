<?php

namespace Beryllium\Cache\Wrapper;

use Psr\SimpleCache\CacheInterface;

class IgnoreThrowablesWrapper implements CacheInterface
{
    public function __construct(protected \Psr\SimpleCache\CacheInterface $client)
    {
    }

    protected function silence(Callable $action, $default = null)
    {
        try {
            return $action();
        } catch (\Throwable) {
        }

        return $default;
    }

    public function get($key, $default = null)
    {
        return $this->silence(
            fn() => $this->client->get($key, $default),
            $default
        );
    }

    public function set($key, $value, $ttl = null)
    {
        return (bool)$this->silence(
            fn() => $this->client->set($key, $value, $ttl)
        );
    }

    public function delete($key)
    {
        return (bool)$this->silence(
            fn() => $this->client->delete($key)
        );
    }

    public function clear()
    {
        return (bool)$this->silence(
            fn() => $this->client->clear()
        );
    }

    public function getMultiple($keys, $default = null)
    {
        return $this->silence(
            fn() => $this->client->getMultiple($keys, $default)
        ) ?? [];
    }

    public function setMultiple($values, $ttl = null)
    {
        return (bool)$this->silence(
            fn() => $this->client->setMultiple($values, $ttl)
        );
    }

    public function deleteMultiple($keys)
    {
        return (bool)$this->silence(
            fn() => $this->client->deleteMultiple($keys)
        );
    }
    public function has($key)
    {
        return (bool)$this->silence(
            fn() => $this->client->has($key)
        );
    }
}