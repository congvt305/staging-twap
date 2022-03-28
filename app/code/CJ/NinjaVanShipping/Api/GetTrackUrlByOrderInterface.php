<?php

namespace CJ\NinjaVanShipping\Api;

interface GetTrackUrlByOrderInterface
{
    /**
     * @param $order
     * @return string
     */
    public function execute($order): string;
}
