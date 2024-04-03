<?php

namespace CJ\PointRedemption\Block;

use CJ\PointRedemption\Setup\Patch\Data\AddRedemptionAttributes;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\Product;
use Magento\Framework\View\Element\Template;

class RedeemableLabel extends Template
{
    const REDEMPTION_LABEL_IMAGE_FILE_PATH = 'CJ_PointRedemption::images/redemption_label.png';

    protected $product;

    protected $registry;

    public function __construct(
        Template\Context            $context,
        \Magento\Framework\Registry $registry,
        array                       $data = []
    )
    {
        $this->registry = $registry;
        parent::__construct($context, $data);
    }

    public function setProduct($product)
    {
        return $this->product = $product;
    }

    /**
     * Get Product
     *
     * @return Product|ProductInterface|null
     */
    public function getProduct()
    {
        if (null === $this->product) {
            $this->product = $this->registry->registry('current_product');
        }

        return $this->product;
    }

    public function isRedeemableProduct(): bool
    {
        $product = $this->getProduct();
        if (!$product) {
            return false;
        }

        return (bool)$this->product->getData(AddRedemptionAttributes::IS_POINT_REDEEMABLE_ATTRIBUTE_CODE);
    }

    public function getRedemptionLabelImage()
    {
        $imageUrl = '';
        if ($this->isRedeemableProduct()) {
            $imageUrl = $this->getViewFileUrl(self::REDEMPTION_LABEL_IMAGE_FILE_PATH);
        }

        return $imageUrl;
    }
}
