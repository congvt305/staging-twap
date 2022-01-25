<?php

namespace CJ\HoverImage\Block;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\ScopeInterface;
use Magento\Catalog\Block\Product\ListProduct;
use Magento\Framework\App\Request\Http;

class HoverImage extends Template
{
    /**
     * @var ListProduct
     */
    protected $listProduct;
    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;
    /**
     * @var Http
     */
    protected $request;
    /**
     * xml config path hover image
     */
    const XML_CONFIG_PATH_HOVER_IMAGE_ACTIVE = 'hover_image/general/active';

    /**
     * default xml layout for hover image
     */
    const XML_DEFAULT_LAYOUT_HOVER_IMAGE = ['catalog_category_view', 'catalogsearch_result_index'];

    /**
     * default hover image attribute
     */
    const HOVER_IMAGE_ATTRIBUTE = 'hover_image';


    /**
     * @param ScopeConfigInterface $scopeConfig
     */

    public function __construct(
        ScopeConfigInterface $scopeConfig,
        ListProduct          $listProduct,
        Template\Context     $context,
        Http                 $request,
        array                $data = []
    ){
        $this->setTemplate('CJ_HoverImage::product/hover_image.phtml');
        $this->request = $request;
        $this->listProduct = $listProduct;
        $this->scopeConfig = $scopeConfig;
        parent::__construct($context, $data);
    }

    /**
     * get data config
     * @return mixed
     */
    public function isHoverImageEnabled()
    {
        $configValue = $this->scopeConfig->getValue(self::XML_CONFIG_PATH_HOVER_IMAGE_ACTIVE, ScopeInterface::SCOPE_WEBSITE);
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
        return $this->listProduct->getImage($product, $imageId, $attributes);
    }

    /**
     * @param $product
     * @return \Magento\Catalog\Block\Product\Image
     */
    public function getHoverImage($product)
    {
        return $this->getImage($product, self::HOVER_IMAGE_ATTRIBUTE);
    }

    /**
     * check is hover image enabled layout
     * @return bool
     */
    public function isHoverImageEnabledLayout() {
        $xmlLayout = $this->request->getFullActionName();
        if(in_array($xmlLayout,self::XML_DEFAULT_LAYOUT_HOVER_IMAGE)) {
            return true;
        }
        return false;
    }

    /**
     * check has hover image
     * @param $productHoverImage
     * @return bool
     */
    public function hasHoverImage($productHoverImage) {
        if(!strpos($productHoverImage->getImageUrl(), 'placeholder')) {
            return true;
        }
        return false;
    }

    /**
     * show hover image
     * @return void
     */
    public function showHoverImage()
    {
        // check for catalog_category_view.xml and catalogsearch_result_index.xml (default layout enabled hover image function)
        if($this->isHoverImageEnabledLayout()) {
            return true;
        }
        //check config
        if ($this->isHoverImageEnabled()) {
            return true;
        }
        return false;

    }

}
