<?php

namespace Beryllium\Cache\Statistics;

/**
 * Class for unified statistics control
 *
 * @package
 * @version $id$
 * @author Yaroslav Nechaev <mail@remper.ru>
 * @license See LICENSE.md
 */
class Statistics
{
    protected $hits;
    protected $misses;
    protected $additionalData = array();

    /**
     * Create statistics object based on raw data
     *
     * @param int $hits
     * @param int $misses
     */
    public function __construct($hits = 0, $misses = 0)
    {
        $this->hits = $hits;
        $this->misses = $misses;
    }

    /**
     * Hits
     *
     * @return int
     */
    public function getHits()
    {
        return $this->hits;
    }

    /**
     * Misses
     *
     * @return int
     */
    public function getMisses()
    {
        return $this->misses;
    }

    /**
     * Get helpfulness percentage
     *
     * @return float
     */
    public function getHelpfulness()
    {
        if ($this->hits + $this->misses === 0) {
            return 0.00;
        }

        return number_format(($this->hits / ($this->hits + $this->misses)) * 100, 2);
    }

    /**
     * @param array $additionalData
     */
    public function setAdditionalData($additionalData)
    {
        $this->additionalData = $additionalData;
    }

    /**
     * @return array
     */
    public function getAdditionalData()
    {
        return $this->additionalData;
    }

    /**
     * @return array
     */
    public function getFormattedArray()
    {
        return array_merge(
            $this->getAdditionalData(),
            array(
                'Hits' => $this->getHits(),
                'Misses' => $this->getMisses(),
                'Helpfulness' => $this->getHelpfulness()
            )
        );
    }
}