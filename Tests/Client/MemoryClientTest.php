<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Cache;
use Beryllium\Cache\Client\MemoryClient;
use PHPUnit\Framework\TestCase;
use Psr\SimpleCache\CacheInterface;

class MemoryClientTest extends TestCase
{
    /**
     * @dataProvider setHasGetProvider
     */
    public function testSetHasGet($key, $value, $ttl, $wait, $expected): void
    {
        $client = $this->getTestClient();
        $this->assertFalse($client->has($key));
        $this->assertTrue($client->set($key, $value, $ttl));
        $this->assertTrue($client->has($key));
        sleep($wait);
        $this->assertSame($expected, $client->get($key));
    }

    public function setHasGetProvider(): ?\Generator
    {
        yield 'simple' => [
            'test',
            'working',
            300,
            0,
            'working',
        ];
    }

    /**
     * @dataProvider expirationProvider
     */
    public function testExpiration($key, $value, $ttl, $wait, $expected): void
    {
        $client = $this->getTestClient();
        $client->set($key, $value, $ttl);
        $this->assertSame($value, $client->get($key));
        sleep($wait);
        $this->assertSame($expected, $client->get($key));
    }

    public function expirationProvider(): ?\Generator
    {
        yield '1-second-exactly' => [
            'test',
            'working',
            1,
            1,
            'working',
        ];

        yield '1-second-beyond' => [
            'test',
            'working',
            1,
            2,
            null,
        ];
    }

    public function testDelete(): void
    {
        $client = $this->getTestClient();
        $client->set('test', 'working', 300);
        $this->assertTrue($client->has('test'));
        $this->assertTrue($client->delete('test'), 'delete call failed');
        $this->assertFalse($client->has('test'), 'delete action failed');
    }

    public function testClear(): void
    {
        $client = $this->getTestClient();
        $client->set('test', 'working', 300);
        $client->set('test2', 'working2', 300);
        $this->assertTrue($client->has('test'));
        $this->assertTrue($client->has('test2'));
        $this->assertTrue($client->clear(), 'clear call failed');
        $this->assertFalse($client->has('test'), 'clear action failed');
        $this->assertFalse($client->has('test2'), 'clear action failed');
    }

    /**
     * @dataProvider multipleProvider
     */
    public function testMultiple($values, $ttl, $default, $expected): void
    {
        $client = $this->getTestClient();
        $client->setMultiple($values, $ttl);

        foreach ($values as $key => $value) {
            $this->assertTrue($client->has($key));
        }

        $actual = $client->getMultiple(array_keys($expected), $default);
        $this->assertSame($expected, $actual);
    }

    public function multipleProvider(): ?\Generator
    {
        yield 'simple' => [
            [
                'test'  => 'working',
                'test2' => 'working2',
            ],
            300,
            'weekend',
            [
                'test'  => 'working',
                'test2' => 'working2',
                'test3' => 'weekend',
            ]
        ];
    }

    public function testDeleteMultiple(): void
    {
        $client = $this->getTestClient();
        $client->set('test', 'working', 300);
        $client->set('test2', 'working2', 300);
        $client->set('test3', 'working3', 300);
        $client->set('test4', 'working4', 300);

        $this->assertTrue($client->deleteMultiple(['test2', 'test4']));
        $this->assertTrue($client->has('test'));
        $this->assertFalse($client->has('test2'));
        $this->assertTrue($client->has('test3'));
        $this->assertFalse($client->has('test4'));
    }

    public function getTestClient(): CacheInterface
    {
        return new MemoryClient();
    }
}