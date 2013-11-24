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
interface ServerVerifierInterface
{
    /**
     * @param string $ip
     * @param int $port
     *
     * @return bool
     */
    public function verify($ip, $port);
}
