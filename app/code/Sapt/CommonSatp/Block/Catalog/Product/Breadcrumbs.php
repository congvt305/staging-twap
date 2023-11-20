<?php
// @codingStandardsIgnoreFile

namespace Sapt\CommonSapt\Block\Catalog\Product;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\Store;

class Breadcrumbs extends \Magento\Framework\View\Element\Template
{
    /**
     * Catalog data
     *
     * @var Data
     */
    protected $_catalogData = null;

    /**
     * @param Context $context
     * @param Data $catalogData
     * @param array $data
     */
    public function __construct(Context $context,
        Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        array $data = []
    )
    {
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectmanager;
        parent::__construct($context, $data);
    }

    /**
     * Retrieve HTML title value separator (with space)
     *
     * @param null|string|bool|int|Store $store
     * @return string
     */
    public function getTitleSeparator($store = null)
    {
        $separator = (string)$this->_scopeConfig->getValue('catalog/seo/title_separator', \Magento\Store\Model\ScopeInterface::SCOPE_STORE, $store);
        return ' ' . $separator . ' ';
    }


    public function getCategory ($product) {

        $_categoryFactory = $this->_objectManager->create('Magento\Catalog\Model\CategoryFactory');

        // for multiple categories, select only the first one
        // remember, index = 0 is 'Default' category
        if (! $product->getCategoryIds())
            return null;

        if (isset ( $product->getCategoryIds()[1]))
            $categoryId = $product->getCategoryIds()[1] ;
        else
            $categoryId = $product->getCategoryIds()[0] ;

        // Additionally for other types of attributes (select, multiselect, ..)
        $category = $_categoryFactory->create()->load($categoryId);

        return ['label' => $category->getName(), 'url' => $category->getUrl() ] ;

    }

    /**
     * Preparing layout
     *
     * @return \Magento\Catalog\Block\Breadcrumbs
     */
    public function getBreadcrumbs()
    {
        $product = $this->_coreRegistry->registry('current_product');

        $breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs');

        if ($breadcrumbsBlock = $this->getLayout()->getBlock('breadcrumbs')) {
            $path = $this->_catalogData->getBreadcrumbPath();

            if (!empty($product)) {
                $foundCatPath=false;
                foreach ($path as $name => $breadcrumb) {
                    if ( strpos ( $name, 'category' ) > -1 )
                        $foundCatPath=true;
                }

                // append the category path
                if (! $foundCatPath) {
                    $productCategory = $this->getCategory($product) ;
                    if ($productCategory) {
                        $categoryPath = [ 'category' => ['label' =>  $productCategory['label'] , 'link' =>  $productCategory['url']]  ];
                        $path = array_merge ($categoryPath ,$path );
                    }
                }
            }

            array_unshift($path, [
                'name' => 'home',
                'label' => 'Home',
                'link' => $this->_storeManager->getStore()->getBaseUrl(),
                'title' => 'Go to Home Page'
            ]);

            return $path;
        }
    }
}
