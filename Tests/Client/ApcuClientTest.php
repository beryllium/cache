<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\ApcuClient;
use PHPUnit\Framework\TestCase;

class ApcuClientTest extends TestCase
{
    public function testInstantiation(): void
    {
        if (!\extension_loaded('apcu') || !ini_get('apc.enable_cli')) {
            $this->markTestSkipped('APCu extension is not loaded/enabled. "php -d apc.enable_cli=1" must be used on the command line to enable this test.');
        }

        $key    = 'pid-' . getmypid();
        $client = new ApcuClient();
        $this->assertInstanceOf(ApcuClient::class, $client);
        $this->assertFalse($client->has($key), 'APCu unexpectedly already has key ' . $key);
        $this->assertTrue($client->set($key, 'working'), 'Failed to set APCu value for key ' . $key);
        $this->assertSame('working', $client->get($key), 'APCu did not contain expected value for key ' . $key);
    }
}