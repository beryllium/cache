<?php

namespace Beryllium\Cache\Client\ServerVerifier;

/**
 * Requirements for a server verifier implementation
 *
 * @package
 * @version $id$
 * @author Jeremy Livingston <jeremyjlivingston@gmail.com>
 * @license See LICENSE.md
 */
class MemcacheServerVerifier implements ServerVerifierInterface
{
    private $ttl = 0.2;

    /**
     * Spend a few tenths of a second opening a socket to the requested IP and port
     *
     * The purpose of this is to verify that the server exists before trying to add it,
     * to cut down on weird errors when doing ->get().
     *
     * @param string $ip IP address (or hostname, possibly)
     * @param int $port Port that memcache is running on
     * @return bool True if the socket opens successfully, or false if it fails
     */
    public function verify($ip, $port)
    {
        $errorNumber = null;
        $errorString = null;
        $fp = @fsockopen($ip, $port, $errorNumber, $errorString, $this->ttl);

        if ($fp) {
            fclose($fp);

            return true;
        }

        return false;
    }
}
