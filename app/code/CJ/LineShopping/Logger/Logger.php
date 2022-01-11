<?php

namespace CJ\LineShopping\Logger;

class Logger extends \Monolog\Logger
{
    const ORDER_POST_BACK = 'ORDER POST BACK';
    const FEE_POST_BACK = 'FEE POST BACK';
    const EXPORT_FEED_DATA = 'EXPORT FEED DATA';
}
