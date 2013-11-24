<?php

namespace Beryllium\Cache\Statistics\Manager;

use Beryllium\Cache\Statistics;

/**
 * Interface for managing statistics
 *
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
interface StatisticsManagerInterface
{
    /**
     * @return Statistics[]
     */
    public function getStatistics();
}