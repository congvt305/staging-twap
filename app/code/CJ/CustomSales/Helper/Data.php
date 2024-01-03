<?php

namespace CJ\CustomSales\Helper;

use Magento\Framework\App\Helper\Context;
use Magento\Checkout\Model\Session;
use Magento\Quote\Api\CartRepositoryInterface;
use Amasty\Promo\Helper\Item as PromoHelper;
use Magento\SalesRule\Model\Rule;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var Session
     */
    protected $checkoutSession;

    /**
     * @var CartRepositoryInterface
     */
    protected $cartRepository;

    /**
     * @var PromoHelper
     */
    protected $promoHelper;

    /**
     * @param Context $context
     * @param Session $checkoutSession
     * @param CartRepositoryInterface $cartRepository
     * @param PromoHelper $promoHelper
     */
    public function __construct(
        Context $context,
        Session $checkoutSession,
        CartRepositoryInterface $cartRepository,
        PromoHelper $promoHelper
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->cartRepository = $cartRepository;
        $this->promoHelper = $promoHelper;
        parent::__construct($context);
    }

    /**
     * @param $rule
     * @return float|int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function isValidExcludeSkuRule($rule)
    {
        $quote = $this->checkoutSession->getQuote();
        if ($quote->getId()) {
            $quoteItems = $quote->getItems();
            foreach ($quoteItems as $item) {
                $productSku = $this->getProductSkuOfItem($item);
                if (!in_array($productSku, $this->getExcludeSkusOfRule($rule))){
                    return true;
                }
            }

            if ($rule->getCouponType() == Rule::COUPON_TYPE_SPECIFIC){
                $couponCode = $rule->getCouponCode();
                $curCouponCode = ltrim(\Safe\preg_replace("/(,?)$couponCode/", '', $quote->getCouponCode()), ',');
                $quote->setCouponCode($curCouponCode)->collectTotals()->save();
            }
        }

        return false;
    }

    /**
     * @param $rule
     * @return array|string[]
     */
    public function getExcludeSkusOfRule($rule)
    {
        $exludeSkus = [];
        if ($rule->getData("exclude_skus")) {
            $exludeSkus = explode(",", $rule->getData("exclude_skus"));
        }
        $exludeSkus = array_map('trim', $exludeSkus);
        return $exludeSkus;
    }

    /**
     * @param $item
     * @return mixed
     */
    public function getProductSkuOfItem($item)
    {
        switch ($item->getProductType()){
            case 'bundle':
                $productSku = $item->getProduct()->getData('sku');
                break;
            case 'simple':
                $productSku = $item->getSku();
                if ($item->getParentItemId()){
                    if ($item->getParentItem()->getProduct()->getTypeId() == 'bundle'){
                        $productSku = $item->getParentItem()->getProduct()->getData('sku');
                    } else {
                        $productSku = $item->getProduct()->getSku();
                    }
                }
                break;
            default:
                $productSku = $item->getSku();
                break;
        }
        return $productSku;
    }
}