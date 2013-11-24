<?php

namespace Beryllium\Cache\Statistics\Manager;

use Beryllium\Cache\Statistics\Statistics;

/**
 * APC statistics manager implementation
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class APCStatisticsManager implements StatisticsManagerInterface
{
    private $safe;

    public function __construct()
    {
        $this->safe = extension_loaded('apc');
    }

    /**
     * @return Statistics[]
     */
    public function getStatistics()
    {
        if (!$this->safe) {
            return array();
        }

        $apcInfo = apc_cache_info('user', true);

        return array('APC' => new Statistics($apcInfo['num_hits'], $apcInfo['num_misses']));
    }
}