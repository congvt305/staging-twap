<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Plugin\Customer\Helper\Address;

use Magento\Customer\Helper\Address;

class SetMinValue
{
    public function afterGetStreetLines(
        Address $subject,
        $result,
        $store = null
    ) {
        if ($result <= 1) {
            $result = 2;
        }

        return $result;
    }
}
