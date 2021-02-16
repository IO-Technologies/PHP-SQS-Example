<?php

namespace Log;

use Psr\Log\AbstractLogger;

/**
 * Class SimpleLogger
 * @package Log
 */
class SimpleLogger extends AbstractLogger
{
    /**
     * {@inheritDoc}
     */
    public function log($level, $message, array $context = array())
    {
        echo "$level $message" . PHP_EOL;
    }
}
