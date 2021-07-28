<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: adeel
 * Date: 29/6/21
 * Time: 12:55 PM
 */
namespace Amore\GcrmDataExport\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Class to defien file for logs
 *
 * Class Handler
 */
class Handler extends Base
{
    /**
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * @var string
     */
    protected $fileName = 'var/log/gcrm_data_export.log';

}
