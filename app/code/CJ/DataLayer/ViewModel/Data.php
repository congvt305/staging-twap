<?php

namespace CJ\DataLayer\ViewModel;


class Data implements \Magento\Framework\View\Element\Block\ArgumentInterface
{
    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;



    const XML_PATH_MIN_QTY_ALLOW = 'cataloginventory/item_options/min_sale_qty';

    /**
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getMinQtyAllow(): int
    {
        return (int)$this->scopeConfig->getValue(self::XML_PATH_MIN_QTY_ALLOW);
    }
}
