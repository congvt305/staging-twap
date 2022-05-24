<?php

namespace CJ\GAEnhanced\ViewModel;

use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;
use \Magento\Framework\App\Request\Http;
class GetProductPosition implements ArgumentInterface
{
    /**
     * Constants for XML Paths.
     */
    const XML_PATH_PRODUCT_PER_PAGE = 'catalog/frontend/grid_per_page';

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var Http
     */
    private $request;


    /**
     * @param ScopeConfigInterface $scopeConfig
     * @param Http $request
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig,
        Http $request
    ) {
        $this->scopeConfig = $scopeConfig;
        $this->request = $request;
    }

    /**
     * Get Product per page
     *
     * @return int|mixed
     */
    public function getProductPerPage()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_PRODUCT_PER_PAGE, ScopeInterface::SCOPE_STORE);
    }

    /**
     * Get current page
     *
     * @return int|mixed
     */
    public function getCurrentPage()
    {
        return $this->request->getParam('p') ? $this->request->getParam('p') : 1;
    }

}
