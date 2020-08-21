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
    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $checkoutSession;

    public function __construct(
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
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
        $this->checkoutSession = $checkoutSession;
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

    public function getJoinName()
    {
        return '가입완료';
    }

    public function getCartData()
    {
        //\Magento\GoogleTagManager\Block\ListJson::getCartContent
        $cartData = [
            'apCartPrice' => '',
            'apCartProdPrice' => '',
            'apCartDiscount' => '',
            'apCartProds' => [],

        ];
        $quote = $this->getCheckoutSession()->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $quote->getAllItems();
        if (count($allItems) < 1) {
            return $cartData;
        }
        $cartData['apCartPrice'] = intval($quote->getSubtotalWithDiscount());
        $cartData['apCartProdPrice'] = intval($this->getOriginalTotal($allItems)); //here to fix, calculate original price
        $cartData['apCartDiscount'] = $cartData['apCartProdPrice'] - $cartData['apCartPrice'];
        foreach ($allItems as $item) {
            if ($item->getProductType() !== 'simple') {
                continue;
            }
            $cartData['apCartProds'][] = $this->jsonSerializer->serialize($this->formatProduct($item));
        }
        return $cartData;
    }
    public function getOrderData()
    {
        $orderData = [
            'AP_PURCHASE_PRICE' => '',
            'AP_PURCHASE_PRDPRICE' => '',
            'AP_PURCHASE_DCTOTAL' => '',
            'AP_PURCHASE_DCBASIC' => '',
            'AP_PURCHASE_COUPON' => '',
            'AP_PURCHASE_MEMBERSHIP' => '',
            'AP_PURCHASE_GIFTCARD' => '',
            'AP_PURCHASE_POINT' => '',
            'AP_PURCHASE_ONLINEGIFT' => '',
            'AP_PURCHASE_ORDERNUM' => '',
            'AP_PURCHASE_BEAUTYACC' => '',
            'AP_PURCHASE_SHIPPING' => '',
            'AP_PURCHASE_TYPE' => '',
            'AP_PURCHASE_COUPONNAME' => '',
            'AP_PURCHASE_PRDS' => []
        ];
        /** @var \Magento\Sales\Model\Order $order */
        $order = $this->getOrder();
        $allItems = $order->getAllItems();
        if (count($allItems) < 1) {
            return $orderData;
        }
        $orderData['AP_PURCHASE_PRICE'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_PRDPRICE'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_DCTOTAL'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_DCBASIC'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_COUPON'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_ORDERNUM'] = intval($order->getSubtotal());
        $orderData['AP_PURCHASE_SHIPPING'] = intval($order->getSubtotal());
        foreach ($allItems as $item) {
            if ($item->getProductType() !== 'simple') {
                continue;
            }
            $orderData['AP_PURCHASE_PRDS'][] = $this->jsonSerializer->serialize($this->formatProduct($item));
        }
        return $orderData;
        
        
        
        
    }
    

    private function getCheckoutSession()
    {
        if (!$this->checkoutSession->isSessionExists()) {
            $this->checkoutSession->start();
        }
        return $this->checkoutSession;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item $item
     * @return array
     */
    private function formatProduct($item)
    {
        $product = $item->getProduct();
        $sku = $product->getSku();
        $product = [];
        $product['name'] = $item->getName();
        $product['code'] = $item->getSku();
        $product['sapcode'] = $item->getSku();
        $product['brand'] = $this->helper->getSiteName() ?? '';
        $product['prdprice'] = intval($item->getProduct()->getPrice());
        $product['discalprice'] = intval($item->getDiscountCalculationPrice());  //test value
        $product['price'] = intval($product['prdprice'] - $item->getDiscountAmount());
        $product['quantity'] = $item->getQty();
        $product['variant'] = '';
        $product['promotion'] = '';
        $product['cate'] = '';
        $product['catecode'] = '';
        return $product;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] | \Magento\Sales\Model\Order\Item[] $allItems
     */
    protected function getOriginalTotal($allItems)
    {
        $dynamicBundleItemIds = [];
        $total = 0;
        /** @var \Magento\Quote\Model\Quote\Item | \Magento\Sales\Model\Order\Item$item */
        foreach ($allItems as $item) {
            //dynamic bundle
            if ($item->getProductType() === 'bundle' && $item->getTaxPercent() === null)
            {
                $dynamicBundleItemIds[] = $item->getItemId();
                continue;
            }
            if ($item->getParentItemId()) {
                continue;
            }
            $total += $item->getProduct()->getPrice() * $item->getQty();
        }
        foreach ($allItems as $item) {
            if (!$item->getParentItemId() || !in_array($item->getParentItemId(), $dynamicBundleItemIds)) {
                continue;
            }
            $total += $item->getProduct()->getPrice() * $item->getQty();
        }
        return $total;
    }



}
