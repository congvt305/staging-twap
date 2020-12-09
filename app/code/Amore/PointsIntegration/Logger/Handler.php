<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-09
 * Time: 오후 2:21
 */

namespace Amore\PointsIntegration\Logger;

use MonoLog\Logger;

class Handler extends \Magento\Framework\Logger\Handler\Base
{
    protected $loggerType = Logger::INFO;

    protected $fileName = '/var/log/points-integration.log';
}
