<?php

namespace Beryllium\Cache\Statistics\Manager;

use Beryllium\Cache\Statistics\Statistics;

/**
 * Memcache statistics manager implementation
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class MemcacheStatisticsManager implements StatisticsManagerInterface
{
    private $memcache;

    /**
     * @param \Memcache $memcache
     */
    public function __construct(\Memcache $memcache)
    {
        $this->memcache = $memcache;
    }

    /**
     * @return Statistics[]
     */
    public function getStatistics()
    {
        $result = array();

        foreach ($this->memcache->getExtendedStats() as $key => $stat_array) {
            $stats = new Statistics($stat_array['get_hits'], $stat_array['get_misses']);

            $stats->setAdditionalData(
                array(
                    'Open connections' => $stat_array['curr_connections'],
                    'Uptime' => $stat_array['uptime']
                )
            );

            $result[$key] = $stats;
        }

        return $result;
    }
}