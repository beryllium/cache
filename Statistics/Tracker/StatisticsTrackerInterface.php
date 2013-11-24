<?php

namespace Beryllium\Cache\Statistics\Tracker;

use Beryllium\Cache\Statistics;

/**
 * Interface for tracking cache statistics
 *
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
interface StatisticsTrackerInterface
{
    /**
     * Add a hit to the tracker
     */
    public function addHit();

    /**
     * Add a miss to the tracker
     */
    public function addMiss();

    /**
     * Write the current statistics to a persistence layer
     *
     * @return bool
     */
    public function write();
}