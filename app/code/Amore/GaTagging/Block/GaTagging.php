<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/19/20
 * Time: 8:57 AM
 */

namespace Amore\GaTagging\Block;

use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class GaTagging extends \Magento\Framework\View\Element\Template
{
    const PURCHASE_DATA_REGISTRY_NAME = 'purchase_data';
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
    /**
     * @var CollectionFactory
     */
    private $selectionCollectionFactory;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order
     */
    private $orderResource;
    /**
     * @var \Magento\Sales\Model\OrderFactory
     */
    private $orderFactory;


    /**
     * GaTagging constructor.
     * @param \Magento\Sales\Model\OrderFactory $orderFactory
     * @param \Magento\Sales\Model\ResourceModel\Order $orderResource
     * @param CollectionFactory $selectionCollectionFactory
     * @param LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Json $jsonSerializer
     * @param \Magento\Framework\Registry $registry
     * @param \Amore\GaTagging\Helper\Data $helper
     * @param Template\Context $context
     * @param array $data
     */
    public function __construct(
        \Magento\Sales\Model\OrderFactory $orderFactory,
        \Magento\Sales\Model\ResourceModel\Order $orderResource,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $selectionCollectionFactory,
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
        $this->selectionCollectionFactory = $selectionCollectionFactory;
        $this->orderResource = $orderResource;
        $this->orderFactory = $orderFactory;
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
//        $productTypesAttr = $product->getCustomAttribute($attributeCode);
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
           $productData[] = ['name' => $product->getName(), 'brand' => $this->helper->getSiteName()];
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
            'AP_CART_PRICE' => 0,
            'AP_CART_PRDPRICE' => 0,
            'AP_CART_DISCOUNT' => 0,
            'AP_CART_PRDS' => [],

        ];
        $quote = $this->getCheckoutSession()->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $quote->getAllItems();
        if (count($allItems) < 1) {
            return $cartData;
        }
        $cartData['AP_CART_PRICE'] = intval($quote->getSubtotalWithDiscount());
        $realItemsData = $this->getRealItemsData($allItems, 'quote');
        $cartProds = 0;
        foreach ($realItemsData as $item) {
            $cartProds += $item['prdprice'] * $item['quantity'];
            $cartData['AP_CART_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $cartData['AP_CART_PRDPRICE'] = intval($cartProds); //sum of original price
        $cartData['AP_CART_DISCOUNT'] = $cartData['AP_CART_PRDPRICE'] - $cartData['AP_CART_PRICE'];

        return $cartData;
    }

    /**
     * @param \Magento\Quote\Model\Quote\Item[] | \Magento\Sales\Model\Order\Item[] $allItems
     * @param string $entityType
     * @return array
     */
    private function getRealItemsData($allItems, $entityType)
    {
        $products = [];
        $allItemsArr = [];
        foreach ($allItems as $item) {
            $allItemsArr[] = $item->getData();
        }
        foreach ($allItems as $item) {
            $product = [];
            if ($item->getProductType() !== 'simple') {
                continue;
            }
            $product['name'] = $item->getName();
            $product['code'] = $item->getSku();
            $product['sapcode'] = $item->getSku();
            $product['brand'] = $this->helper->getSiteName() ?? '';
            $product['prdprice'] = intval($item->getProduct()->getPrice());
            $product['price'] =  intval($item->getPrice()); // // cat rule applied, need an attention 얼마에 팔았냐? 일단 로우토탈을 qty로 나눈다.
            $product['quantity'] = $entityType === 'order' ? intval($item->getQtyOrdered()) : intval($item->getQty());
            $product['variant'] =  '';
            $product['promotion'] = '';
            $product['cate'] = '';
            $product['catecode'] = '';

            if ($item->getParentItemId()) {
                //common child
                $parentItem = $allItems[array_search($item->getParentItemId(), array_column($allItemsArr, 'item_id'))];
                if ($parentItem->getAppliedRuleIds()) {
                    $product['promotion'] = $parentItem->getAppliedRuleIds();
                }
                if ($entityType === 'quote') {
                    $product['quantity'] = intval($product['quantity'] * $parentItem->getQty());
                }
                //dynamic bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() === null) {
                    $product['price'] =  intval($item->getPrice()); // cat rule applied
                }
                //fixed bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() !== null) {

                    $bundleSelectionItems = $entityType === 'order' ? $this->getBundleSelectionsFromOrderItem($parentItem) : $this->getBundleSelectionsFromQuoteItem($parentItem);
                    $productId = $item->getData('product_id');
                    $bundleSelectionTotal = 0;
                    foreach ($bundleSelectionItems as $bundleSelectionItem) {
                        $bundleSelectionTotal += $bundleSelectionItem['price'] * $bundleSelectionItem['qty'];
                    }
                    $proportionRate = $bundleSelectionItems[$productId]['price'] / $bundleSelectionTotal; //전체 가격중에 이 상품이 차지하는 가격비율. 할인전 개당 가격 /전체 가격(할인전) * qty
                    $product['price'] =  intval($proportionRate * $parentItem->getPrice()); //cart rule not applied
                }
                //configurable's child
                if ($parentItem->getProductType() === 'configurable') {
                    $product['price'] =  intval($parentItem->getPrice()); // cat rule applied
                    $nameArr = explode(' ', $item->getName());
                    $product['variant'] = $nameArr[(count($nameArr) - 1)];
                }
            }
            $products[] = $product;
        }
        return $products;
    }

    /**
     * @param \Magento\Sales\Model\Order\Item $parentItem
     */
    private function getBundleSelectionsFromOrderItem($parentItem)
    {
        $selectionItems = [];
        $bundleOptions = $parentItem->getProductOptionByCode('bundle_options');
        $bundleOptionIds = array_keys($bundleOptions);
        $selectionCollection = $this->selectionCollectionFactory->create();
        $selectionCollection->setOptionIdsFilter($bundleOptionIds);
        foreach ($selectionCollection as $selection) {
            $selectionItems[$selection->getData('product_id')] = ['price' => $selection->getData('selection_price_value'), 'qty' => $selection->getData('selection_qty')];
        }
        return $selectionItems;
    }

    private function getBundleSelectionsFromQuoteItem($parentItem)
    {
        $selectionItems = [];
        $selections = $parentItem->getOptionsByCode();
        $selectionIds = explode(',',str_replace(['"', '[', ']'], '', $selections['bundle_selection_ids']->getValue()));
        $selectionCollection = $this->selectionCollectionFactory->create();
        $selectionCollection->setSelectionIdsFilter($selectionIds);

        foreach ($selectionCollection as $selection) {
            $selectionItems[$selection->getData('product_id')] = ['price' => $selection->getData('selection_price_value'), 'qty' => $selection->getData('selection_qty')];
        }
        return $selectionItems;
    }

    //체크아웃 페이지
    public function getOrderData()
    {
        $orderData = [
            'AP_ORDER_PRICE' => 0,
            'AP_ORDER_PRDPRICE' => 0,
            'AP_ORDER_DISCOUNT' => 0,
            'AP_ORDER_PRDS' => []
        ];
        $quote = $this->getCheckoutSession()->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $quote->getAllItems();
        if (count($allItems) < 1) {
            return $orderData;
        }
        $realItemsData = $this->getRealItemsData($allItems, 'quote');
        $orderProdPrice = 0;
        foreach ($realItemsData as $item) {
            $orderProdPrice += $item['prdprice'] * $item['quantity'];
            $orderData['AP_ORDER_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $orderData['AP_ORDER_PRICE'] = intval($quote->getSubtotalWithDiscount());
        $orderData['AP_ORDER_PRDPRICE'] = intval($orderProdPrice);
        $orderData['AP_ORDER_DISCOUNT'] = intval($orderData['AP_ORDER_PRDPRICE'] - $orderData['AP_ORDER_PRICE']);
        return $orderData;
    }
    //주문완료 페이지

    /**
     * @return array
     */
    public function getPurchaseData()
    {
        $orderData = [
            'AP_PURCHASE_PRICE' => 0,
            'AP_PURCHASE_PRDPRICE' => 0,
            'AP_PURCHASE_DCTOTAL' => 0,
            'AP_PURCHASE_DCBASIC' => 0, //?? 문의할것.
            'AP_PURCHASE_COUPON' => 0,
            'AP_PURCHASE_MEMBERSHIP' => 0,
            'AP_PURCHASE_GIFTCARD' => 0,
            'AP_PURCHASE_POINT' => 0,
            'AP_PURCHASE_ONLINEGIFT' => 0,
            'AP_PURCHASE_ORDERNUM' => 0,
            'AP_PURCHASE_BEAUTYACC' => 0,
            'AP_PURCHASE_SHIPPING' => 0,
//            'AP_PURCHASE_TYPE' => '',
//            'AP_PURCHASE_COUPONNAME' => '',
            'AP_PURCHASE_PRDS' => []
        ];
        /** @var \Magento\Sales\Model\Order $order */
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderFactory->create();
        $this->orderResource->load($order, $orderId);
        $allItems = $order->getAllItems();
        if (count($allItems) < 1) {
            return $orderData;
        }
        $realItemsData = $this->getRealItemsData($allItems, 'order');
        $orderProdPrice = 0;
        foreach ($realItemsData as $item) {
            $orderProdPrice += $item['prdprice'] * $item['quantity'];
            $orderData['AP_PURCHASE_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $orderData['AP_PURCHASE_PRICE'] = intval($order->getGrandTotal());
        $orderData['AP_PURCHASE_PRDPRICE'] = intval($orderProdPrice);
        $orderData['AP_PURCHASE_SHIPPING'] = intval($order->getShippingAmount()) ?? 0;
        $orderData['AP_PURCHASE_DCTOTAL'] = intval($orderData['AP_PURCHASE_PRDPRICE'] - $orderData['AP_PURCHASE_PRICE'] - $orderData['AP_PURCHASE_SHIPPING']);
        if ($orderData['AP_PURCHASE_DCTOTAL'] > 0) {
            $orderData['AP_PURCHASE_DCBASIC'] = $order->getAppliedRuleIds() ?? $order->getIncrementId(); //?? 이상함 문의할것.
        }
        $orderData['AP_PURCHASE_COUPON'] = $order->getCouponCode() ? intval($order->getDiscountAmount()) : 0; //여기가 복병이네.. 쿠폰할인가라??
        $orderData['AP_PURCHASE_ORDERNUM'] = $order->getIncrementId();
        return $orderData;
    }
    
    private function getCheckoutSession()
    {
        if (!$this->checkoutSession->isSessionExists()) {
            $this->checkoutSession->start();
        }
        return $this->checkoutSession;
    }
}

