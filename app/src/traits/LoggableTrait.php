<?php

namespace Helium\traits;

use Exception;
use Monolog\Formatter\LineFormatter;
use Monolog\Handler\StreamHandler;
use Monolog\Logger;

trait LoggableTrait
{
    /**
     * @var Logger
     */
    private $logger;

    protected function setupLogging(string $logName, string $logDir = '.')
    {
        $logPath = $logDir.'/' . $logName . '.log';
        $lineFormat = "[%datetime%] %channel%.%level_name%: %message% %context%\n";
        $dateFormat = "Y-m-d H:i:s";
        $this->logger = new Logger($logName);

        try {
            $handler = new StreamHandler($logPath);
            $formatter = new LineFormatter($lineFormat, $dateFormat, false, true);
            $handler->setFormatter($formatter);
            $this->logger->pushHandler($handler);
        } catch (Exception $e) {
            $this->logger = null;
        }
    }

    protected function log(string $message, $contents, int $level = Logger::INFO): void
    {
        if ($this->logger instanceof Logger) {
            if(!is_array($contents)) {
                $contents = [$contents];
            }
            $this->logger->addRecord($level, $message, $contents);
        }
    }

}