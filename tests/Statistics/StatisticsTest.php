<?php

namespace Beryllium\Cache\Tests\Statistics;

use Beryllium\Cache\Statistics\Statistics;
use PHPUnit\Framework\TestCase;

/**
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class StatisticsTest extends TestCase
{
    public function testUnitializedHitsEqualZero()
    {
        $statistics = new Statistics();
        $this->assertEquals(0, $statistics->getHits());
    }

    public function testHitsAreInitialized()
    {
        $hits = 20;
        $statistics = new Statistics($hits);

        $this->assertEquals($hits, $statistics->getHits());
    }

    public function testUnitializedMissesEqualZero()
    {
        $statistics = new Statistics();
        $this->assertEquals(0, $statistics->getMisses());
    }

    public function testMissesAreInitialized()
    {
        $misses = 20;
        $statistics = new Statistics(0, $misses);

        $this->assertEquals($misses, $statistics->getMisses());
    }

    public function testAdditionalDataIsSet()
    {
        $additionalData = array('Average TTL' => 40);

        $statistics = new Statistics();
        $statistics->setAdditionalData($additionalData);

        $this->assertEquals($additionalData, $statistics->getAdditionalData());
    }
}
