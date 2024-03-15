<?php

namespace CJ\PointRedemption\Plugin;

use CJ\PointRedemption\Helper\Data;
use Laminas\Di\Exception\LogicException;
use Magento\Checkout\Model\Cart;
use Magento\Customer\Model\Session;
use \CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use \Amore\PointsIntegration\Model\CustomerPointsSearch;
use Magento\Framework\Exception\LocalizedException;

class PointValidation
{
    /**
     * @var Session
     */
    protected Session $customerSession;

    /**
     * @var CustomerPointsSearch
     */

    protected CustomerPointsSearch $customerPointsSearch;

    /**
     * @var Data
     */
    protected Data $pointRedemptionHelper;

    public function __construct(
        Session $customerSession,
        CustomerPointsSearch $customerPointsSearch,
        Data $pointRedemptionHelper
    ) {
        $this->customerSession = $customerSession;
        $this->pointRedemptionHelper = $pointRedemptionHelper;
    }

    /**
     * @param Cart $subject
     * @param $productInfo
     * @param null $requestInfo
     * @return array
     * @throws LocalizedException
     */
    public function beforeAddProduct(Cart $subject, $productInfo, $requestInfo = null)
    {
        $isLogin = $this->customerSession->isLoggedIn();
        $isRedeemableProduct = $productInfo->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
        $isPointRedemption = $requestInfo['is_point_redemption'] ?? false;
        $isRedemptionItem = $isRedeemableProduct && $isPointRedemption;
        if ($subject->getItemsCount()) {
            if(($this->isRedemptionProductInCart($subject) && !$isRedemptionItem) || (!$this->isRedemptionProductInCart($subject) && $isRedemptionItem)) {
                throw new LocalizedException(
                    __("You can't buy redemption product and normal product in same time")
                );
            }
        }
        if ($isRedemptionItem) {
            if (!$isLogin) {
                throw new LocalizedException(
                    __("Please login into your account to continue shopping")
                );
            }
            $qty = $requestInfo['qty'] ?? 1;
            $pointAmount =
                $productInfo->getData(
                    AddRedemptionAttributes::POINT_REDEMPTION_AMOUNT_ATTRIBUTE_CODE
                ) * $qty;
            $this->pointRedemptionHelper->validatePointBalance($pointAmount, null);
        }

        return [$productInfo, $requestInfo];
    }

    /**
     * check cart have item redemption
     * @param $cart
     * @return boolean
     */
    public function isRedemptionProductInCart($cart)
    {
        foreach ($cart->getQuote()->getAllItems() as $item) {
            if ($item->getIsPointRedeemable()) {
                return true;
            }
        }
        return false;
    }
}
