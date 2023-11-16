<?php

declare(strict_types=1);

namespace Amasty\ShopbySeo\Model\UrlParser\Utils;

use Amasty\ShopbySeo\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;

class AttributeAliasReplacer
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var Data
     */
    private $seoHelper;

    public function __construct(
        StoreManagerInterface $storeManager,
        Data $seoHelper
    ) {
        $this->storeManager = $storeManager;
        $this->seoHelper = $seoHelper;
    }

    /**
     * Replace all existed attribute aliases in seo part request string
     *
     * @param string $seoPart
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function replace(string $seoPart): string
    {
        $store = $this->storeManager->getStore()->getId();
        
        /* Need for preparing attributes url aliases */
        $this->seoHelper->getSeoSignificantAttributeCodes();

        $replaces = [];
        foreach ($this->seoHelper->getAttributeUrlAliases() as $attributeCode => $alias) {
            if (!empty($alias[$store])) {
                $replaces[$attributeCode] = $alias[$store];
            }
        }

        return str_replace(array_values($replaces), array_keys($replaces), $seoPart);
    }
}
