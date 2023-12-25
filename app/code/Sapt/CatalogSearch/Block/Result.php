<?php


namespace Sapt\CatalogSearch\Block;

use Magento\Catalog\Model\Layer\Resolver as LayerResolver;
use Magento\CatalogSearch\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Magento\Search\Model\QueryFactory;
use Magento\Store\Model\StoreManagerInterface;

class Result extends \Magento\CatalogSearch\Block\Result
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var QueryFactory
     */
    private $queryFactory;

    public function __construct(
        StoreManagerInterface $storeManager,
        Context $context,
        LayerResolver $layerResolver,
        Data $catalogSearchData,
        QueryFactory $queryFactory,
        array $data = []
    ) {
        parent::__construct($context, $layerResolver, $catalogSearchData, $queryFactory, $data);
        $this->queryFactory = $queryFactory;
        $this->storeManager = $storeManager;
    }

    /**
     * Get search query text
     *
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryText()
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            return __("'%1' Results", $this->catalogSearchData->getEscapedQueryText());
        }

        return parent::getSearchQueryText();
    }

    /**
     * @return \Magento\Framework\Phrase
     */
    public function getSearchQueryContentTitle()
    {
        return __("<span>'%1'</span> Results", $this->catalogSearchData->getEscapedQueryText());
    }

    public function getBlockTitle()
    {
        return __('Search Results (%1)', $this->getTotalCount());
    }

    public function getProductCountText()
    {
        return __('Product (%1)', $this->getResultCount());
    }

    public function getBlogCountText()
    {
        return __('Post (%1)', $this->getBlogCount());
    }

    /**
     * @return int
     */
    protected function getTotalCount()
    {
        return (int)$this->getResultCount() + $this->getBlogCount();
    }

    /**
     * @return int
     */
    protected function getBlogCount()
    {
        $type = $this->getRequest()->getParam('search_type', 'product');
        if (strtolower($type) == 'blog') {
            return 5;
        }
        return 0;
    }
}
