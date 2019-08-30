<?php

namespace Beryllium\Cache\Tests;

use Beryllium\Cache\Cache;
use Beryllium\Cache\Client\ApcuClient;
use Beryllium\Cache\Tests\Client\MemoryClientTest;
use Psr\SimpleCache\CacheInterface;

/**
 * Class CacheMemoryClientTest wraps the MemoryClient in a Cache object
 * and then runs it through all of the normal MemoryClient tests.
 *
 * The expectation is 100% identical behaviour given the same input.
 *
 * It does not flex the advanced functionality of the Cache class,
 * such as default TTL or key prefixing.
 */
class CacheMemoryClientTest extends MemoryClientTest
{
    public function getTestClient(): CacheInterface
    {
        $client = parent::getTestClient();

        return new Cache($client);
    }
}