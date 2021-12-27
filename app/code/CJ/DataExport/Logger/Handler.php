<?php

namespace CJ\DataExport\Logger;

use Monolog\Logger;

/**
 * Class Handler
 */
class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = 'var/log/cj_data_export.log';
}
