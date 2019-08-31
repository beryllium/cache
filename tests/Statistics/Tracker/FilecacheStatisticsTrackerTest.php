<?php

namespace Beryllium\Cache\Tests\Statistics\Tracker;

use Beryllium\Cache\Statistics\Tracker\FilecacheStatisticsTracker;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class FilecacheStatisticsTrackerTest extends TestCase
{
    private $path;
    private $root;
    private $filename;

    public function setUp(): void
    {
        $this->path = 'test-path';
        $this->filename = '__stats';
        $this->root = vfsStream::setup($this->path);
    }

    public function testStatisticsFileIsCreated()
    {
        $this->assertFalse($this->root->hasChild($this->filename));

        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->write();

        $this->assertTrue($this->root->hasChild($this->filename));
    }

    public function testStatisticsFileHasZeroValues()
    {
        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->write();

        $expectedContents = array('hits' => 0, 'misses' => 0);

        $this->assertEquals(serialize($expectedContents), $this->root->getChild($this->filename)->getContent());
    }

    public function testStatisticsFileHasIncrementedHits()
    {
        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->addHit();
        $tracker->addHit();

        $tracker->write();
        $writtenData = unserialize($this->root->getChild($this->filename)->getContent());

        $this->assertEquals(2, $writtenData['hits']);
    }

    public function testStatisticsFileHasIncrementedMisses()
    {
        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->addMiss();
        $tracker->addMiss();

        $tracker->write();
        $writtenData = unserialize($this->root->getChild($this->filename)->getContent());

        $this->assertEquals(2, $writtenData['misses']);
    }

    public function testExistingFileIsInitialized()
    {
        $content = array('hits' => 20, 'misses' => 10);

        $file = vfsStream::newFile($this->filename);
        $file->withContent(serialize($content));

        $this->root->addChild($file);

        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->write();

        $file = $this->root->getChild($this->filename);
        $result = unserialize($file->getContent());

        $this->assertEquals($content, $result);
    }

    public function testExistingFileHasIncrementedHits()
    {
        $content = array('hits' => 20, 'misses' => 10);

        $file = vfsStream::newFile($this->filename);
        $file->withContent(serialize($content));

        $this->root->addChild($file);

        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->addHit();
        $tracker->addHit();
        $tracker->write();

        $writtenData = unserialize($this->root->getChild($this->filename)->getContent());

        $this->assertEquals(22, $writtenData['hits']);
    }

    public function testExistingFileHasIncrementedMisses()
    {
        $content = array('hits' => 20, 'misses' => 10);

        $file = vfsStream::newFile($this->filename);
        $file->withContent(serialize($content));

        $this->root->addChild($file);

        $tracker = new FilecacheStatisticsTracker(vfsStream::url($this->path));
        $tracker->addMiss();
        $tracker->addMiss();
        $tracker->write();

        $writtenData = unserialize($this->root->getChild($this->filename)->getContent());

        $this->assertEquals(12, $writtenData['misses']);
    }
}
