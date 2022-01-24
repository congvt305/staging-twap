<?php

namespace CJ\HoverImage\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Block\Product\ListProduct;

class HoverImage extends Template
{
    /**
     * @var ListProduct
     */
    protected $listProduct;
    /**
     * @var
     */
    protected $product;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     *
     */
    const XML_CONFIG_PATH = 'hover_image/general/active';

    /**
     * @var string
     */
    protected $_template = 'CJ_HoverImage::product/hover_image.phtml';

    /**
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ListProduct $listProduct,
        Template\Context     $context,
        array                $data = []
    ){
        $this->setTemplate('CJ_HoverImage::product/hover_image.phtml');
        $this->listProduct = $listProduct;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * get data config
     * @return mixed
     */
    public function isEnable()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_CONFIG_PATH,ScopeInterface::SCOPE_WEBSITE);
        return $configValue;
    }

    /**
     * get hover image of product
     * @param $product
     * @param $imageId
     * @param $attributes
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getImage($product, $imageId, $attributes = [])
    {
        return $this->listProduct->getImage($product,$imageId,$attributes);
    }

}
