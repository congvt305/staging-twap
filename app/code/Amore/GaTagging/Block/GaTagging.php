<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/19/20
 * Time: 8:57 AM
 */

namespace Amore\GaTagging\Block;


use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Psr\Log\LoggerInterface;

class GaTagging extends \Magento\Framework\View\Element\Template
{
    /**
     * @var \Amore\GaTagging\Helper\Data
     */
    private $helper;
    /**
     * @var \Magento\Framework\Registry
     */
    private $registry;
    /**
     * @var Json
     */
    private $jsonSerializer;
    /**
     * @var \Magento\Customer\Model\Session
     */
    private $customerSession;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Registry $registry,
        \Amore\GaTagging\Helper\Data $helper,
        Template\Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->registry = $registry;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
    }

    /**
     * Render GA tracking scripts
     *
     * @return string
     */
    protected function _toHtml()
    {
        if (!$this->helper->isActive()) {
            return '';
        }
        return parent::_toHtml();
    }

    public function getChannel()
    {
        return 'PC';
    }
    public function getBreadCrumbText() //todo: javascript
    {
        $crumbBlock =  $this->_layout->getBlock('breadcrumbs');

        if($crumbBlock) {
            $this->logger->debug('crumb exist');
            $html = $crumbBlock->toHtml();
        }

//        $crumbs = $crumbBlock->getCrumbs();

//        $result = '';
//        foreach ($crumbs as $crumb) {
//            $result . $crumb->getText();
//        }
//        return $result;
        return 'home';
    }

    public function getTitle()
    {
        return $this->pageConfig->getTitle()->get();
    }

    /**
     * @return \Magento\Catalog\Model\Product
     */
    public function getCurrentProduct() {
        $registryName = 'current_product';
        return $this->registry->registry($registryName);
    }

    /**
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getProductCategory($product) {
        $attributeCode = 'product_types';
        $productTypesAttr = $product->getCustomAttribute($attributeCode);
//        return $productTypesAttr->getFrontend()->getValue($product);
        return '스킨케어';
    }

    public function getQueryText()
    {
        return $this->_request->getParam('q');
    }

    public function getResultProductData()
    {
       $resultProducts = $this->_layout->getBlock('search_result_list')->getLoadedProductCollection();
       $productData = [];
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($resultProducts as $product) {
           $productData[] = ['name' => $product->getName(), 'brand' => 'Laneige'];
       }
        return $this->jsonSerializer->serialize($productData);

    }

    public function getSearchNumber()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        $collection = $this->_layout->getBlock('search_result_list')->getLoadedProductCollection();
        return $collection->getSize();
    }
    public function getSearchType()
    {
        return '직접입력';
    }

    public function getCustomerRegisterSuccess()
    {
        if ($this->customerSession->getData('customer_register_success', true)) {
            return true;
        }
        return false;
    }

    public function getJoinName()
    {
        if ($this->getCustomerRegisterSuccess()) {
            return '가입완료';
        }
        return '';
    }


}
