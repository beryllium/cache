<?php

namespace Beryllium\Cache\Tests\Wrapper;

use Beryllium\Cache\Wrapper\IgnoreThrowablesWrapper;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class IgnoreThrowablesWrapperTest extends TestCase
{
    public function testGet()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertSame(
            'default-value',
            $cache->get('key-does-not-matter', 'default-value')
        );
    }

    public function testSet()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse(
            $cache->set('key-does-not-matter', 'default-value')
        );
    }

    public function testDelete()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse(
            $cache->delete('key-does-not-matter')
        );
    }

    public function testClear()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse($cache->clear());
    }

    /**
     * The ideal behaviour here is a bit nuanced.
     *
     * If there's a failure setting an individual key, do we want
     * to return false for the whole attempt, or just return the
     * default value for that key's array position?
     *
     * Going with the former for now.
     */
    public function testGetMultiple()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertSame(
            [],
            $cache->getMultiple(
                ['key-does-not-matter', 'still-does-not-matter'],
                'default-value'
            )
        );
    }

    public function testSetMultiple()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse(
            $cache->setMultiple(
                ['key-does-not-matter' => 'val1']
            )
        );
    }

    public function testDeleteMultiple()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse(
            $cache->deleteMultiple(
                ['key-does-not-matter', 'still-does-not-matter']
            )
        );
    }

    public function testHas()
    {
        $cache = new IgnoreThrowablesWrapper($this->getNoisyClass());

        $this->assertFalse(
            $cache->has('key-does-not-matter')
        );
    }

    protected function getNoisyClass()
    {
        return new class implements CacheInterface {
            public function get($key, $default = null)
            {
                throw new \RuntimeException('test');
            }

            public function set($key, $value, $ttl = null)
            {
                throw new \RuntimeException('test');
            }

            public function delete($key)
            {
                throw new \RuntimeException('test');
            }

            public function clear()
            {
                throw new \RuntimeException('test');
            }

            public function getMultiple($keys, $default = null)
            {
                throw new \RuntimeException('test');
            }

            public function setMultiple($values, $ttl = null)
            {
                throw new \RuntimeException('test');
            }

            public function deleteMultiple($keys)
            {
                throw new \RuntimeException('test');
            }

            public function has($key)
            {
                throw new \RuntimeException('test');
            }
        };
    }
}