<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 24/5/21
 * Time: 5:36 PM
 */
namespace Eguana\RedInvoice\Logger;

use Magento\Framework\Logger\Handler\Base;
use Monolog\Logger;

/**
 * Logger file name Log type
 * Class Handler
 */
class Handler extends Base
{
    /**
     * Logging level
     * @var int
     */
    protected $loggerType = Logger::INFO;

    /**
     * File name
     * @var string
     */
    protected $fileName = '/var/log/red_invoice.log';
}
