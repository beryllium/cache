<?php

namespace Beryllium\Cache\Exception;

use Psr\SimpleCache\InvalidArgumentException as InvalidArgumentExceptionInterface;

class InvalidArgumentException extends \RuntimeException implements InvalidArgumentExceptionInterface
{

}