<?php

namespace Beryllium\Cache\Tests\Statistics\Manager;

use Beryllium\Cache\Statistics\Manager\FilecacheStatisticsManager;
use org\bovigo\vfs\vfsStream;
use PHPUnit\Framework\TestCase;

/**
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class FilecacheStatisticsManagerTest extends TestCase
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

    public function testMissingFileReturnsStatisticsObject()
    {
        $manager = new FilecacheStatisticsManager(vfsStream::url($this->path));
        $result = $manager->getStatistics();

        $this->assertInstanceOf('\Beryllium\Cache\Statistics\Statistics', $result['File cache']);
    }

    public function testExistingFileReturnsStatisticsObject()
    {
        $file = vfsStream::newFile($this->filename);
        $this->root->addChild($file);

        $manager = new FilecacheStatisticsManager(vfsStream::url($this->path));
        $result = $manager->getStatistics();

        $this->assertInstanceOf('\Beryllium\Cache\Statistics\Statistics', $result['File cache']);
    }
}