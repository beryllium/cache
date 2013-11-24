<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Statistics\Tracker\StatisticsTrackerInterface;

/**
 * Uses the filesystem to store and retrieve cache entries
 *
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class FilecacheClient implements ClientInterface
{
    private $path;

    /** @var \Beryllium\Cache\Statistics\Tracker\StatisticsTrackerInterface */
    private $statisticsTracker;

    /**
     * @param string|null $path
     */
    public function __construct($path)
    {
        if (empty($path)) {
            return;
        }

        if (!is_dir($path) && !mkdir($path)) {
            return;
        }

        if (!is_writable($path)) {
            return;
        }

        $this->path = $path;
        if (substr($this->path, -1) !== '/') {
            $this->path .= '/';
        }
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get($key)
    {
        if (!$this->isSafe() || empty($key)) {
            return false;
        }

        if (!file_exists($this->getFilename($key))) {
            $this->incrementAndWriteStatistics(false);

            return false;
        }

        $file = unserialize(file_get_contents($this->getFilename($key)));

        if (!is_array($file) || $file['key'] !== $key) {
            $this->incrementAndWriteStatistics(false);

            return false;
        }

        if ($file['ttl'] != 0 && time() - $file['ctime'] > $file['ttl']) {
            $this->incrementAndWriteStatistics(false);
            $this->delete($key);

            return false;
        }

        $this->incrementAndWriteStatistics(true);

        return unserialize($file['value']);
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl)
    {
        $file = array(
            'key'   => $key,
            'value' => serialize($value),
            'ttl'   => $ttl,
            'ctime' => time(),
        );

        if ($this->isSafe() && !empty($key)) {
            return (bool) file_put_contents($this->getFilename($key), serialize($file));
        }

        return false;
    }

    /**
     * @param string $key
     * @return bool
     */
    public function delete($key)
    {
        $filename = $this->getFilename($key);

        if (file_exists($filename)) {
            unlink($filename);

            return true;
        }

        return false;
    }

    /**
     * @return bool
     */
    public function isSafe()
    {
        return !is_null($this->path);
    }

    /**
     * @param StatisticsTrackerInterface $statisticsTracker
     */
    public function setStatisticsTracker(StatisticsTrackerInterface $statisticsTracker)
    {
        $this->statisticsTracker = $statisticsTracker;
    }

    /**
     * @param bool $hit
     */
    private function incrementAndWriteStatistics($hit)
    {
        if (!$this->statisticsTracker) {
            return;
        }

        if ($hit) {
            $this->statisticsTracker->addHit();
        } else {
            $this->statisticsTracker->addMiss();
        }

        $this->statisticsTracker->write();
    }

    /**
     * @param string $key
     * @return string
     */
    private function getFilename($key)
    {
        return $this->path . md5($key) . '_file.cache';
    }
}
