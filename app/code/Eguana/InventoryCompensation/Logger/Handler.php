<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-29
 * Time: 오후 3:22
 */

namespace Eguana\InventoryCompensation\Logger;

use MonoLog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/inventory_compensation.log';
}
