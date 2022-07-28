<?php
declare(strict_types=1);

namespace CJ\SFLocker\Plugin\Model\Carrier\Command;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Quote\Model\Quote\Address\RateRequest;
use Magento\InventoryInStorePickupShipping\Model\Carrier\Command\GetShippingPrice as GetShippingPriceCore;
use Magento\Store\Model\ScopeInterface;
use Magento\Store\Model\StoreManagerInterface;

/**
 * @inheritdoc
 */
class GetShippingPrice
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     *
     */
    const XML_PATH_CART_SUBTOTAL_FREE = 'carriers/instore/total_free';
    /**
     *
     */
    const XML_PATH_MACAU_SHIPPING_FEE = 'carriers/instore/macau_price';
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        StoreManagerInterface $storeManager
    )
    {
        $this->scopeConfig = $scopeConfig;
        $this->storeManager = $storeManager;
    }

    /**
     * @param GetShippingPriceCore $sub
     * @param $result
     * @param RateRequest $rateRequest
     * @return float
     */
    public function afterExecute(GetShippingPriceCore $sub, $result, RateRequest $rateRequest): float
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $totalFree = (float)$this->scopeConfig->getValue(self::XML_PATH_CART_SUBTOTAL_FREE, ScopeInterface::SCOPE_WEBSITE);
            if ($rateRequest->getAllItems()) {
                $freeCartPrice = $rateRequest->getBaseSubtotalWithDiscountInclTax();

                if ($totalFree > 0 && $freeCartPrice >= $totalFree) {
                    return 0.0;
                }
            }
            if ($rateRequest->getDestRegionCode() == 'M') {
                return (float)$this->scopeConfig->getValue(self::XML_PATH_MACAU_SHIPPING_FEE, ScopeInterface::SCOPE_WEBSITE);
            }
        }
        return $result;
    }
}
