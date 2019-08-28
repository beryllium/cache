<?php

namespace Beryllium\Cache\Client;

use Beryllium\Cache\Statistics\Tracker\StatisticsTrackerInterface;
use Psr\SimpleCache\CacheInterface;

/**
 * Uses the filesystem to store and retrieve cache entries
 *
 * @package
 * @version $id$
 * @author Kevin Boyd <beryllium@beryllium.ca>
 * @license See LICENSE.md
 */
class FilecacheClient implements CacheInterface
{
    private $path;

    /** @var \Beryllium\Cache\Statistics\Tracker\StatisticsTrackerInterface */
    private $statisticsTracker;

    /**
     * @param string|null $path
     */
    public function __construct($path)
    {
        $path = rtrim($path, DIRECTORY_SEPARATOR);

        if (empty($path)) {
            return;
        }

        if (!is_dir($path) && !mkdir($path) && !is_dir($path)) {
            return;
        }

        if (!is_writable($path)) {
            return;
        }

        $this->path = $path;
    }

    /**
     * @param string $key
     * @return bool|mixed
     */
    public function get($key, $default = null)
    {
        if (!$this->isSafe() || empty($key)) {
            return $default;
        }

        if (!file_exists($this->getFilename($key))) {
            $this->incrementAndWriteStatistics(false);

            return $default;
        }

        $file = unserialize(file_get_contents($this->getFilename($key)));

        if (!is_array($file) || $file['key'] !== $key) {
            $this->incrementAndWriteStatistics(false);

            return $default;
        }

        if ($file['ttl'] != 0 && time() - $file['ctime'] > $file['ttl']) {
            $this->incrementAndWriteStatistics(false);
            $this->delete($key);

            return $default;
        }

        $this->incrementAndWriteStatistics(true);

        return unserialize($file['value']) ?? $default;
    }

    /**
     * @param string $key
     * @param mixed $value
     * @param int $ttl
     *
     * @return bool
     */
    public function set($key, $value, $ttl = null)
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
        return $this->path . DIRECTORY_SEPARATOR . md5($key) . '_file.cache';
    }

    /**
     * Wipes clean the entire cache's keys.
     *
     * @return bool True on success and false on failure.
     */
    public function clear()
    {
        // TODO: Implement clear() method.
    }

    /**
     * Obtains multiple cache items by their unique keys.
     *
     * @param iterable $keys    A list of keys that can obtained in a single operation.
     * @param mixed    $default Default value to return for keys that do not exist.
     *
     * @return iterable A list of key => value pairs. Cache keys that do not exist or are stale will have $default as value.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function getMultiple($keys, $default = null)
    {
        // TODO: Implement getMultiple() method.
    }

    /**
     * Persists a set of key => value pairs in the cache, with an optional TTL.
     *
     * @param iterable               $values A list of key => value pairs for a multiple-set operation.
     * @param null|int|\DateInterval $ttl    Optional. The TTL value of this item. If no value is sent and
     *                                       the driver supports TTL then the library may set a default value
     *                                       for it or let the driver take care of that.
     *
     * @return bool True on success and false on failure.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $values is neither an array nor a Traversable,
     *   or if any of the $values are not a legal value.
     */
    public function setMultiple($values, $ttl = null)
    {
        // TODO: Implement setMultiple() method.
    }

    /**
     * Deletes multiple cache items in a single operation.
     *
     * @param iterable $keys A list of string-based keys to be deleted.
     *
     * @return bool True if the items were successfully removed. False if there was an error.
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if $keys is neither an array nor a Traversable,
     *   or if any of the $keys are not a legal value.
     */
    public function deleteMultiple($keys)
    {
        // TODO: Implement deleteMultiple() method.
    }

    /**
     * Determines whether an item is present in the cache.
     *
     * NOTE: It is recommended that has() is only to be used for cache warming type purposes
     * and not to be used within your live applications operations for get/set, as this method
     * is subject to a race condition where your has() will return true and immediately after,
     * another script can remove it making the state of your app out of date.
     *
     * @param string $key The cache item key.
     *
     * @return bool
     *
     * @throws \Psr\SimpleCache\InvalidArgumentException
     *   MUST be thrown if the $key string is not a legal value.
     */
    public function has($key)
    {
        // TODO: Implement has() method.
    }
}
