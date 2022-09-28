<?php

namespace App\Log;

use Google\Cloud\Logging\LoggingClient;
use Monolog\Handler\PsrHandler;
use Monolog\Logger;

class CreateGoogleCloudLogger
{
    public function __invoke(array $config)
    {
        $logName = 'sinpex_dashboard';
        $logging = new LoggingClient([
            'projectId' => config('logging.googleProjectId')
        ]);
        $psrLogger = $logging->psrLogger($logName);

        $handler = new PsrHandler($psrLogger);
        return new Logger($logName, [$handler]);
    }
}
