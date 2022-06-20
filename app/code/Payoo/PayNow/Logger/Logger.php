<?php

namespace Payoo\PayNow\Logger;

class Logger extends \Monolog\Logger
{
    const TYPE_LOG_REFUND = 'refund';
    const TYPE_LOG_RETURN = 'return';
}
