<?php
/**
 * @author Vendor
 * @copyright Copyright (c) 2019 Vendor (https://www.vendor.com/)
 */
namespace CJ\EventManager\Logger\Handler;

use Magento\Framework\Logger\Handler\Base as BaseHandler;
use Monolog\Logger as MonologLogger;

/**
 * Class ErrorHandler
 */
class ErrorHandler extends BaseHandler
{
    /**
     * Logging level
     *
     * @var int
     */
    protected $loggerType = MonologLogger::INFO;

    /**
     * File name
     *
     * @var string
     */
    protected $fileName = '/var/log/migration_events.log';
}
