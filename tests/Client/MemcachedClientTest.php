<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\MemcachedClient;
use PHPUnit\Framework\TestCase;
use Beryllium\Cache\Client\ServerVerifier\ServerVerifierInterface;

/**
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class MemcachedClientTest extends TestCase
{
    protected \PHPUnit\Framework\MockObject\MockObject $memcache;
    protected \PHPUnit\Framework\MockObject\MockObject $serverVerifier;

    protected function setUp(): void
    {
        $this->memcache = $this->getMockBuilder(\Memcached::class)
            ->getMock();

        $this->serverVerifier = $this->getMockBuilder(ServerVerifierInterface::class)
            ->getMock();
    }

    public function testUnsafeGetReturnsNull(): void
    {
        $client = new MemcachedClient($this->memcache);
        $result = $client->get('test-key');

        $this->assertNull($result);
    }

    public function testAddServerCallsVerifier(): void
    {
        $ip = '127.0.0.1';
        $port = 555;

        $this->serverVerifier->expects($this->once())
            ->method('verify')
            ->with($this->equalTo($ip), $this->equalTo($port));


        $client = new MemcachedClient($this->memcache, $this->serverVerifier);
        $client->addServer($ip, $port);
    }

    public function testVerifierFailureReturnsFalse(): void
    {
        $this->serverVerifier->expects($this->any())
            ->method('verify')
            ->will($this->returnValue(false));

        $client = new MemcachedClient($this->memcache, $this->serverVerifier);
        $result = $client->addServer('127.0.0.1', 555);

        $this->assertFalse($result);
    }

    public function testAddServerCallsMemcached(): void
    {
        $ip = '127.0.0.1';
        $port = 555;

        $this->serverVerifier->expects($this->any())
            ->method('verify')
            ->will($this->returnValue(true));

        $this->memcache->expects($this->once())
            ->method('addServer')
            ->with($this->equalTo($ip), $this->equalTo($port));

        $client = new MemcachedClient($this->memcache, $this->serverVerifier);
        $client->addServer($ip, $port);
    }

    /**
     * @dataProvider addServerResponseProvider
     */
    public function testAddServerResultIsReturned(bool $addServerResult, bool $expectedReturn): void
    {
        $this->serverVerifier->expects($this->any())
            ->method('verify')
            ->will($this->returnValue(true));

        $this->memcache->expects($this->once())
            ->method('addServer')
            ->will($this->returnValue($addServerResult));

        $client = new MemcachedClient($this->memcache, $this->serverVerifier);
        $result = $client->addServer('127.0.0.1', 555);

        $this->assertEquals($expectedReturn, $result);
    }

    public function addServerResponseProvider(): array
    {
        return [
            [true, true],
            [false, false]
        ];
    }

    public function testSafeGetCallsMemcached(): void
    {
        $key = 'test-key';

        $this->memcache->expects($this->once())
            ->method('get')
            ->with($this->equalTo($key));

        $client = $this->getSafeClient();
        $client->get($key);
    }

    public function testSafeSetCallsMemcached(): void
    {
        $key = 'test-key';
        $value = 'test-value';
        $ttl = 1000;

        $this->memcache->expects($this->once())
            ->method('set')
            ->with($this->equalTo($key), $this->equalTo($value), $this->equalTo($ttl));

        $client = $this->getSafeClient();
        $client->set($key, $value, $ttl);
    }

    public function testSafeDeleteCallsMemcached(): void
    {
        $key = 'test-key';

        $this->memcache->expects($this->once())
            ->method('delete')
            ->with($this->equalTo($key));

        $client = $this->getSafeClient();
        $client->delete($key);
    }

    private function getSafeClient(): \Beryllium\Cache\Client\MemcachedClient
    {
        $this->serverVerifier->expects($this->any())
            ->method('verify')
            ->will($this->returnValue(true));

        $this->memcache->expects($this->any())
            ->method('addServer')
            ->will($this->returnValue(true));

        $client = new MemcachedClient($this->memcache, $this->serverVerifier);
        $client->addServer('127.0.0.1', 555);

        return $client;
    }
}
