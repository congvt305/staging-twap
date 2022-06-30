<?php

namespace CJ\NinjaVanShipping\Api;

use Magento\Framework\Exception\LocalizedException;

interface GenerateWaybillInterface
{
    /**
     * @param $trackingNumbers
     * @param $storeId
     * @throws LocalizedException
     * @return mixed
     */
    public function process($trackingNumbers, $storeId);
}
