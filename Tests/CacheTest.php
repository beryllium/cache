<?php

namespace Beryllium\Cache\Tests;

use Beryllium\Cache\Cache;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

/**
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class CacheTest extends TestCase
{
    protected $client;

    protected function setUp(): void
    {
        $this->client = $this->getMockBuilder(CacheInterface::class)
            ->getMock();
    }

    /**
     * @dataProvider keyPrefixProvider
     */
    public function testGetCallsClient($key, $expectedKey, $prefix = null)
    {
        $this->client->expects($this->once())
            ->method('get')
            ->with($this->equalTo($expectedKey));

        $cache = new Cache($this->client);

        if ($prefix) {
            $cache->setPrefix($prefix);
        }

        $cache->get($key);
    }

    /**
     * @dataProvider keyPrefixProvider
     */
    public function testSetCallsClient($key, $expectedKey, $prefix = null)
    {
        $value = 'test-value';
        $ttl = 500;

        $this->client->expects($this->once())
            ->method('set')
            ->with(
                $this->equalTo($expectedKey),
                $this->equalTo($value),
                $this->equalTo($ttl)
            )->willReturn(true);

        $cache = new Cache($this->client);

        if ($prefix) {
            $cache->setPrefix($prefix);
        }

        $cache->set($key, $value, $ttl);
    }

    public function testSetUsesDefaultTtl()
    {
        $this->client->expects($this->once())
            ->method('set')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo(Cache::DEFAULT_TTL)
            )->willReturn(true);

        $cache = new Cache($this->client);
        $cache->set('test-key', 'test-value');
    }

    public function testSetUsesProvidedTtl()
    {
        $ttl = 1200;

        $this->client->expects($this->once())
            ->method('set')
            ->with(
                $this->anything(),
                $this->anything(),
                $this->equalTo($ttl)
            )->willReturn(true);

        $cache = new Cache($this->client);
        $cache->setTtl($ttl);

        $cache->set('test-key', 'test-value');
    }

    /**
     * @dataProvider keyPrefixProvider
     */
    public function testDeleteCallsClient($key, $expectedKey, $prefix = null)
    {
        $this->client->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($expectedKey));

        $cache = new Cache($this->client);

        if ($prefix) {
            $cache->setPrefix($prefix);
        }

        $cache->delete($key);
    }

    public function keyPrefixProvider()
    {
        return array(
            array('test-key', 'test-key'),
            array('test-key', 'prefix_test-key', 'prefix_'),
            array('another-key', 'be--another-key', 'be--'),
        );
    }
}