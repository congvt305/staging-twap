<?php

namespace CJ\CatalogProduct\Helper;

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
    private $catalogLogger;

    /**
     * Logger constructor.
     * @param LoggerInterface $catalogLogger
     */
    public function __construct(
        LoggerInterface $catalogLogger
    )
    {
        $this->catalogLogger = $catalogLogger;
    }

    /**
     * @param Exception $exception
     * @param null $messageLog
     */
    public function logException(Exception $exception, $messageLog = null): void
    {
        $this->catalogLogger->critical(__("Catalog Product Logger _ [%1]", $messageLog),
            [
                'message' => $exception->getMessage(),
                'code' => $exception->getCode()
            ]
        );
    }
}
