<?php

namespace CJ\PointRedemption\Block;

use CJ\PointRedemption\Helper\Data;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\Template;

class FinalPriceAjax extends Template
{
    /**
     * @var Registry
     */
    protected $registry;

    /**
     * @var Data
     */
    protected $pointRedemptionHelper;

    public function __construct(
        Template\Context $context,
        Registry $registry,
        Data $pointRedemptionHelper,
        array $data = []
    ) {
        $this->registry = $registry;
        $this->pointRedemptionHelper = $pointRedemptionHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('pointredemption/ajax/finalprice');
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProductId()
    {
        $product = $this->registry->registry('current_product');
        return $product ? $product->getId() : null;
    }
}
