<?php

namespace CJ\NinjaVanShipping\Logger;

use CJ\Middleware\Logger\Handler as BaseHandler;

class Handler extends BaseHandler
{
    protected $fileName = '/var/log/ninjavanshipping.log';
}
