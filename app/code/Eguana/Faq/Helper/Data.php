<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 *
 * Eguana\Faq\Helper
 */
class Data extends AbstractHelper
{
    /**
     * @return mixed
     */
    public function getFaqEnabled()
    {
        return $this->scopeConfig->getValue('faq/general/enabled', ScopeInterface::SCOPE_STORE);
    }

    /**
     * @return mixed
     */
    public function getFaqTypes()
    {
        return $this->scopeConfig->getValue(
            'faq/category',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * @param $storeCode
     * @return mixed
     */
    public function getStoreCategories($storeCode)
    {
        return $this->scopeConfig->getValue(
            'faq/category',
            ScopeInterface::SCOPE_STORE,
            $storeCode
        );
    }
}
