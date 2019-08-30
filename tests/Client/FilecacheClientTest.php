<?php

namespace Beryllium\Cache\Tests\Client;

use Beryllium\Cache\Client\FilecacheClient;
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

        $this->assertSame(null, $this->cache->get('test'));
    }

    public function testStats()
    {
        $stats   = new FilecacheStatisticsTracker(vfsStream::url('cacheDir'));
        $manager = new FilecacheStatisticsManager(vfsStream::url('cacheDir'));

        $this->cache->setStatisticsTracker($stats);
        $this->cache->get('test');
        $this->cache->set('test', 'testing', 300);
        $this->cache->get('test');

        $data = $manager->getStatistics();

        $this->assertEquals(array('File cache'), array_keys($data));

        $numbers = $data['File cache']->getFormattedArray();

        $this->assertEquals(
            array(
                'Hits'        => 1,
                'Misses'      => 1,
                'Helpfulness' => '50.00'
            ),
            $numbers
        );
    }
}
