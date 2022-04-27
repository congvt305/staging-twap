<?php

namespace CJ\NinjaVanShipping\Logger;

use Amore\Base\Logger\Handler as BaseHandler;

class Handler extends BaseHandler
{
    protected $fileName = '/var/log/ninjavanshipping.log';
}
