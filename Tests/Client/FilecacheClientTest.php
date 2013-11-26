<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\FilecacheClient;
use org\bovigo\vfs\vfsStream;

class FilecacheClientTest extends \PHPUnit_Framework_TestCase
{

    /**
     * @var vfsStreamDirectory
     */
    public $vfs;

    /**
     * @var FilecacheClient
     */
    public $cache;

    public function setUp()
    {
        $this->vfs   = vfsStream::setup('cacheDir');
        $this->cache = new FilecacheClient(vfsStream::url('cacheDir'));
    }

    public function testFilecacheConstruct()
    {
        $this->assertTrue($this->cache->isSafe());

        // @todo Test failure scenarios
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

        $this->assertFalse($this->cache->get('test'));
    }
}
 