<?php
declare(strict_types=1);
namespace CJ\CatalogProduct\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ALLOW_QUICK_BUY_BUNDLE_PRODUCT = 'catalog/bundle_product/allow_quick_buy_bundle_product';


    /**
     * @param Context $context
     */
    public function __construct(Context $context)
    {
        parent::__construct($context);
    }

    /**
     * @param $websiteId
     * @return mixed
     */
    public function isAllowQuickBuyBundleProduct($websiteId = null)
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ALLOW_QUICK_BUY_BUNDLE_PRODUCT, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

}
