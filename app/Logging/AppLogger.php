<?php

namespace App\Logging;


use Monolog\Logger;
use Monolog\Processor\IntrospectionProcessor;

class AppLogger
{
    /**
     * @param \Illuminate\Log\Logger $logger
     */
    public function __invoke($logger)
    {
        foreach ($logger->getHandlers() as $handler) {
            $handler->pushProcessor(new IntrospectionProcessor(config('logging.level'), array("Illuminate\\")));
        }
    }
}
