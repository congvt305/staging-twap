<?php

namespace CJ\Rewards\Logger;

use CJ\Middleware\Logger\Handler as BaseHandler;

class Handler extends BaseHandler
{
    protected $fileName = '/var/log/ordercancel.log';
}
