<?php

namespace CJ\PointRedemption\Plugin;

use Magento\Checkout\Model\Cart;
use \CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Magento\Framework\Exception\LocalizedException;

class CustomOption
{
    /**
     * @param Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        $isRedeemableProduct = $productInfo->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        $isPointRedemption = $requestInfo['is_point_redemption'] ?? false;
        if ($isRedeemableProduct && $isPointRedemption) {
            $productInfo->addCustomOption('is_point_redemption', (int)$isPointRedemption);
        }

        return [$productInfo, $requestInfo];
    }
}
