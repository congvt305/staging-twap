<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-06-28
 * Time: 오후 11:41
 */

namespace Amore\Sap\Logger;

use MonoLog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/sap.log';

}
