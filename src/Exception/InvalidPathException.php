<?php

namespace Beryllium\Cache\Exception;

use Psr\SimpleCache\CacheException;

class InvalidPathException extends \RuntimeException implements CacheException
{
}