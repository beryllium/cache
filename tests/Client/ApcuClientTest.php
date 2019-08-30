<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\ApcuClient;
use PHPUnit\Framework\TestCase;

class ApcuClientTest extends TestCase
{
    public function testInstantiation(): void
    {
        $key    = 'pid-' . getmypid();
        $client = new ApcuClient();
        $this->assertInstanceOf(ApcuClient::class, $client);
        $this->assertFalse($client->has($key), 'APCu unexpectedly already has key ' . $key);
        $this->assertTrue($client->set($key, 'working'), 'Failed to set APCu value for key ' . $key);
        $this->assertSame('working', $client->get($key), 'APCu did not contain expected value for key ' . $key);
    }
}
