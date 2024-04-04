<?php

namespace CJ\Migrate\Helper;

use Exception;
use Psr\Log\LoggerInterface;

/**
 * Class Logger
 * @package CJ\CatalogProduct\Helper
 */
class Logger
{
    /**
     * @var LoggerInterface
     */
    private $migrateLogger;

    /**
     * Logger constructor.
     * @param LoggerInterface $migrateLogger
     */
    public function __construct(
        LoggerInterface $migrateLogger
    )
    {
        $this->migrateLogger = $migrateLogger;
    }

    /**
     * @param Exception $exception
     * @param null $messageLog
     */
    public function logException(Exception $exception, $messageLog = null): void
    {
        $this->migrateLogger->critical(__("Migrate Logger _ [%1]", $messageLog),
            [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]
        );
    }

    /**
     * @param array $info
     * @param string|null $messageLog
     */
    public function logInfo(array $info, string $messageLog = null): void
    {
        $this->migrateLogger->debug(__("Migrate Logger _ [%1]", $messageLog), $info);
    }
}
