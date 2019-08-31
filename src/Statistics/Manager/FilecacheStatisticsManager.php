<?php

namespace Beryllium\Cache\Statistics\Manager;

use Beryllium\Cache\Statistics\Statistics;

/**
 * Filecache statistics manager implementation
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class FilecacheStatisticsManager implements StatisticsManagerInterface
{
    private $path;

    public function __construct($path)
    {
        $this->path = $path;
    }

    /**
     * @return Statistics[]
     */
    public function getStatistics()
    {
        if (!file_exists($this->getFilename())) {
            return array('File cache' => new Statistics());
        }

        $stats = unserialize(file_get_contents($this->getFilename()));

        $hits = isset($stats['hits']) ? $stats['hits'] : 0;
        $misses = isset($stats['misses']) ? $stats['misses'] : 0;

        return array('File cache' => new Statistics($hits, $misses));
    }

    /**
     * Get the filename from the provided path
     *
     * @return string
     */
    private function getFilename()
    {
        return $this->path . DIRECTORY_SEPARATOR . '__stats';
    }
}