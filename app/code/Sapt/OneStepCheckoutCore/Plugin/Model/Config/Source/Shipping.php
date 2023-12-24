<?php
declare(strict_types=1);

namespace Sapt\OneStepCheckoutCore\Plugin\Model\Config\Source;

class Shipping
{

    public function afterToOptionArray(\Amasty\CheckoutCore\Model\Config\Source\Shipping $subject, $result) {
        if (isset($result['blackcat'])) {
            $result['blackcat']['value'] = 'blackcat_homedelivery';
        }
        if (isset($result['gwlogistics'])) {
            $result['gwlogistics']['value'] = 'gwlogistics_CVS';
        }

        return $result;
    }
}
