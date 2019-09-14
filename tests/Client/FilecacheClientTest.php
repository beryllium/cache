<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\FilecacheClient;
use Beryllium\Cache\Exception\InvalidPathException;
use Beryllium\Cache\Statistics\Manager\FilecacheStatisticsManager;
use Beryllium\Cache\Statistics\Tracker\FilecacheStatisticsTracker;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

class FilecacheClientTest extends TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    public $vfs;

    /**
     * @var FilecacheClient
     */
    public $cache;

    public function setUp(): void
    {
        $this->vfs   = vfsStream::setup('cacheDir');
        $this->cache = new FilecacheClient(vfsStream::url('cacheDir'));
    }

    public function testFilecacheConstructNonexistentPath()
    {
        $this->expectException(InvalidPathException::class);
        $this->expectExceptionMessage('Provided path directory does not exist and/or could not be created');

        new FilecacheClient('/tmp/this/folder/does/not/exist');
    }

    public function testSetAndGet()
    {
        $this->cache->set('test', 'testing', 20);

        $this->assertEquals('testing', $this->cache->get('test'));
    }

    public function testDelete()
    {
        $this->assertTrue($this->cache->set('test', 'testing', 20));

        $this->assertTrue($this->cache->delete('test'));

        $this->assertSame(null, $this->cache->get('test'));
    }
}
