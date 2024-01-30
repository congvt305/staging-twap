<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/19/20
 * Time: 8:57 AM
 */

namespace Amore\GaTagging\Block;

use Magento\Bundle\Model\Product\Price;
use Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Sales\Api\OrderRepositoryInterface;
use Psr\Log\LoggerInterface;

class GaTagging extends \Magento\Framework\View\Element\Template
{
    const PURCHASE_DATA_REGISTRY_NAME = 'purchase_data';
    const FORMAT_DATE = 'Y-m-d';
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
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var \Magento\Framework\Message\ManagerInterface
     */
    private $messageManager;
    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * Catalog Product collection
     *
     * @var \Magento\Catalog\Model\ResourceModel\Collection\AbstractCollection
     */
    protected $_productCollection;

    /**
     * @var \Magento\CatalogInventory\Api\StockRegistryInterface
     */
    protected $stockRegistry;

    /**
     * @var \Magento\Framework\Stdlib\DateTime\DateTimeFactory
     */
    protected $dateTimeFactory;

    /**
     * @var \Amore\GaTagging\Model\Ap
     */
    protected $ap;

    /**
     * @var \Magento\Catalog\Helper\Product
     */
    protected $catalogProductHelper;

    /**
     * @var \Magento\Framework\HTTP\Header
     */
    private $header;

    /**
     * @var \Eguana\SocialLogin\Model\SocialLoginHandler
     */
    private $socialLoginModel;

    /**
     * @var \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory
     */
    private $ruleCollectionFactory;

    /**
     * @param ProductRepositoryInterface $productRepository
     * @param \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory
     * @param \Magento\Framework\Message\ManagerInterface $messageManager
     * @param OrderRepositoryInterface $orderRepository
     * @param CollectionFactory $selectionCollectionFactory
     * @param LoggerInterface $logger
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param Json $jsonSerializer
     * @param \Magento\Framework\Registry $registry
     * @param \Amore\GaTagging\Helper\Data $helper
     * @param \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry
     * @param Template\Context $context
     * @param \Amore\GaTagging\Model\Ap $ap
     * @param \Magento\Catalog\Helper\Product $catalogProductHelper
     * @param \Magento\Framework\HTTP\Header $header
     * @param \Eguana\SocialLogin\Model\SocialLoginHandler $socialLoginModel
     * @param \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory
     * @param array $data
     */
    public function __construct(
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        \Magento\Framework\Stdlib\DateTime\DateTimeFactory $dateTimeFactory,
        \Magento\Framework\Message\ManagerInterface $messageManager,
        \Magento\Sales\Api\OrderRepositoryInterface $orderRepository,
        \Magento\Bundle\Model\ResourceModel\Selection\CollectionFactory $selectionCollectionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Serialize\Serializer\Json $jsonSerializer,
        \Magento\Framework\Registry $registry,
        \Amore\GaTagging\Helper\Data $helper,
        \Magento\CatalogInventory\Api\StockRegistryInterface $stockRegistry,
        Template\Context $context,
        \Amore\GaTagging\Model\Ap $ap,
        \Magento\Catalog\Helper\Product $catalogProductHelper,
        \Magento\Framework\HTTP\Header $header,
        \Eguana\SocialLogin\Model\SocialLoginHandler $socialLoginModel,
        \Magento\SalesRule\Model\ResourceModel\Rule\CollectionFactory $ruleCollectionFactory,
        array $data = []
    ) {
        $this->stockRegistry = $stockRegistry;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->ap = $ap;
        $this->catalogProductHelper = $catalogProductHelper;
        parent::__construct($context, $data);
        $this->helper = $helper;
        $this->registry = $registry;
        $this->jsonSerializer = $jsonSerializer;
        $this->customerSession = $customerSession;
        $this->logger = $logger;
        $this->checkoutSession = $checkoutSession;
        $this->selectionCollectionFactory = $selectionCollectionFactory;
        $this->orderRepository = $orderRepository;
        $this->messageManager = $messageManager;
        $this->productRepository = $productRepository;
        $this->header = $header;
        $this->socialLoginModel = $socialLoginModel;
        $this->ruleCollectionFactory = $ruleCollectionFactory;
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

    public function getCurrentCategory()
    {
        return $this->registry->registry('current_category');
    }

    /**
     * todo get Category Data
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getProductCategory($product) {
        return $this->helper->getProductCategory($product);
    }

    /**
     * get Root Category
     *
     * @return mixed|null
     */
    public function getRooCategory()
    {
        if ($this->getCurrentCategory()) {
            if ($this->getCurrentCategory()->getParentCategories()) {
                foreach ($this->getCurrentCategory()->getParentCategories() as $parent) {
                    if ($parent->getLevel() == 2) {
                        // return the level 2 category name;
                        return $parent->getName();
                    }
                }
            }
        }
        return '韓系保養';
    }

    public function getQueryText()
    {
        return $this->_request->getParam('q');
    }

    public function getResultProductData()
    {
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->getLayout()->getBlock('search_result_list')->getLoadedProductCollection();
        }
        $productData = [];
        $index = 1;
        /** @var \Magento\Catalog\Model\Product $product */
        foreach ($this->_productCollection as $product) {

            $productData[] = [
                'code' => $product->getSku(),
                'name' => $product->getName(),
                'brand' => $this->helper->getSiteName(),
                'cate' => $this->helper->getProductCategory($product),
                'index' => $index++,
                'item_list_name' => 'SEARCH_RESULT',
                'apg_brand_code' => substr($product->getSku(), 0, 5),
                'price' => intval($product->getFinalPrice()),
                'discount' => intval($product->getPrice() - $product->getFinalPrice()),
                'prdprice' => intval($product->getPrice())
            ];
        }
        return $this->jsonSerializer->serialize($productData);

    }

    public function getSearchNumber()
    {
        /** @var \Magento\Catalog\Model\ResourceModel\Product\Collection $collection */
        if ($this->_productCollection === null) {
            $this->_productCollection = $this->getLayout()->getBlock('search_result_list')->getLoadedProductCollection();
        }
        return $this->_productCollection->getSize();
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
            'AP_ECOMM_CURRENCY' => $this->getCurrentCurrencyCode()
        ];
        $quote = $this->getCheckoutSession()->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $quote->getAllItems();
        if (count($allItems) < 1) {
            return $cartData;
        }
        $cartData['AP_CART_PRICE'] = intval($quote->getSubtotalWithDiscount());
        $realItemsData = $this->getQuoteRealParentItemsData($allItems);
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
     * @param \Magento\Catalog\Model\Product $product
     */
    public function getProductInfo($product) {
        return $this->ap->getProductInfo($product);
    }

    /**
     * @param \Magento\Sales\Model\Order\Item[] $allItems
     * @return array
     */
    private function getOrderRealItemsData($allItems)
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
            $product['v2code'] = $item->getProductId();
            $product['sapcode'] = $item->getSku();
            $product['brand'] = $this->helper->getSiteName() ?? '';
            $product['prdprice'] = intval($item->getProduct()->getPrice());
            $product['price'] =  intval($item->getPrice()); // // cat rule applied, need an attention 얼마에 팔았냐? 일단 로우토탈을 qty로 나눈다.
            $product['quantity'] =intval($item->getQtyOrdered());
            $product['variant'] =  '';
            $product['promotion'] = ''; //todo simple promotion??
            $product['cate'] = $this->helper->getProductCategory($item->getProduct());
            $product['catecode'] = '';
            $product['url'] = $item->getProduct()->getProductUrl();
            $product['img_url'] = $this->catalogProductHelper->getThumbnailUrl($item->getProduct());

            if ($item->getParentItemId()) {
                //common child
                $parentItem = $allItems[array_search($item->getParentItemId(), array_column($allItemsArr, 'item_id'))];
                if ($parentItem->getAppliedRuleIds()) {
                    $product['promotion'] = $parentItem->getAppliedRuleIds();
                }
                //dynamic bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() === null) {
                    $product['price'] =  intval($item->getPrice()); // cat rule applied
                }
                //fixed bundle's child
                if ($parentItem->getProductType() === 'bundle' && $parentItem->getTaxPercent() !== null) {
                    $bundleSelectionItems = $this->getBundleSelectionsFromOrderItem($parentItem);
                    $productId = $item->getData('product_id');
                    $bundleSelectionTotal = 0;
                    $hasChidlenPrice = true;
                    foreach ($bundleSelectionItems as $bundleSelectionItem) {
                        $bundleSelectionTotal += $bundleSelectionItem['price'] * $bundleSelectionItem['qty'];
                    }
                    if (intval($bundleSelectionTotal) === 0) {
                        $hasChidlenPrice = false;
                        $children = $parentItem->getChildrenItems();
                        foreach ($children as $child) {
                                $childProduct = $this->productRepository->getById($child->getProductId());
                                $bundleSelectionTotal += $childProduct->getPrice() * $child->getQtyOrdered() / $parentItem->getQtyOrdered();
                        }
                        if ($bundleSelectionTotal == 0) {
                            $bundleSelectionTotal = $item->getPrice();
                        }
                        $proportionRate = $item->getPrice() / $bundleSelectionTotal;
                    }

                    if ($hasChidlenPrice) {
                        $proportionRate = $bundleSelectionItems[$productId]['price'] / $bundleSelectionTotal;
                    }
                     //전체 가격중에 이 상품이 차지하는 가격비율. 할인전 개당 가격 /전체 가격(할인전) * qty
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
     * Get Parent product when product is bundle or configurable
     *
     * @param $allItems
     * @return array
     */
    private function getQuoteRealParentItemsData($allItems)
    {
        $products = [];
        foreach ($allItems as $item) {
            $product = [];
            if ($item->getParentItemId()) {
                continue;
            }
            $product['name'] = $item->getName();
            $product['code'] = $item->getSku();
            $product['sapcode'] = $item->getSku();
            $product['brand'] = $this->helper->getSiteName() ?? '';
            $product['quantity'] = intval($item->getQty());
            $product['variant'] = '';
            if ($item->getAppliedRuleIds()) {
                $product['promotion'] = $item->getAppliedRuleIds();
            }
            $currentProduct = $item->getProduct();
            $product['cate'] = $this->helper->getProductCategory($currentProduct);
            $product['url'] = $item->getProduct()->getProductUrl();
            $product['img_url'] = $this->catalogProductHelper->getThumbnailUrl($currentProduct);
            $product['catecode'] = '';
            $product['apg_brand_code'] = substr($item->getSku(), 0, 5);
            $product['prdprice'] = $item->getRowTotal();
            $product['discount'] = $item->getDiscountAmount();
            if ($item->getProductType() === 'configurable') {
                $nameArr = explode(' ', $item->getName());
                $product['variant'] = $nameArr[(count($nameArr) - 1)];
            }
            if ($item->getProductType() === 'bundle') {
                if ($currentProduct->getPriceType() == Price::PRICE_TYPE_DYNAMIC) {
                    foreach($item->getChildren() as $childrenItem) {
                        // no need to reset because parent discount always 0
                        $product['discount'] += $childrenItem->getDiscountAmount();
                    }
                }
            }
            $product['price'] = $product['prdprice'] - $product['discount']; // // cat rule applied, need an attention 얼마에 팔았냐? 일단 로우토탈을 qty로 나눈다.
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
        $optionIds = explode(',',str_replace(['"', '[', ']'], '', $selections['bundle_option_ids']->getValue()));
        $selectionCollection = $this->selectionCollectionFactory->create();
        $selectionCollection->setSelectionIdsFilter($selectionIds);
        $selectionCollection->setOptionIdsFilter($optionIds);
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
            'AP_ECOMM_CURRENCY' => $this->getCurrentCurrencyCode(),
            'AP_ORDER_PRDS' => []
        ];
        $quote = $this->getCheckoutSession()->getQuote();
        /** @var \Magento\Quote\Model\Quote\Item[] $allItems */
        $allItems = $quote->getAllItems();
        if (count($allItems) < 1) {
            return $orderData;
        }
        $realItemsData = $this->getQuoteRealParentItemsData($allItems);
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
            'AP_PURCHASE_CURRENCY' => '',
            'AP_PURCHASE_PRDS' => []
        ];
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $orderId = $this->checkoutSession->getLastOrderId();
        $order = $this->orderRepository->get($orderId);
        if (!$order) {
            return $orderData;
        }
        $allItems = $order->getAllItems();
        $realItemsData = $this->getOrderRealParentItemsData($allItems);
        foreach ($realItemsData as $item) {
            $orderData['AP_PURCHASE_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $orderData['AP_PURCHASE_PRICE'] = $order->getGrandTotal();
        $orderData['AP_PURCHASE_PRDPRICE'] = $order->getSubTotal() + $order->getShippingAmount();
        $orderData['AP_PURCHASE_SHIPPING'] = $order->getShippingAmount();
        $orderData['AP_PURCHASE_DCTOTAL'] = $order->getDiscountAmount();
        if ($orderData['AP_PURCHASE_DCTOTAL'] > 0) {
            $orderData['AP_PURCHASE_DCBASIC'] = $order->getAppliedRuleIds() ?? $order->getIncrementId(); //?? 이상함 문의할것.
        }
        $orderData['AP_PURCHASE_COUPON'] = $order->getCouponCode() ? intval($order->getDiscountAmount()) : 0; //여기가 복병이네.. 쿠폰할인가라??
        $orderData['AP_PURCHASE_ORDERNUM'] = $order->getIncrementId();
        $orderData['AP_PURCHASE_CURRENCY'] = $this->getCurrentCurrencyCode();
        $orderData['AP_PURCHASE_DATE'] = $this->dateTimeFactory->create()->gmtDate(self::FORMAT_DATE, $order->getCreatedAt());
        $ruleData = $this->getRuleName($order->getAppliedRuleIds());
        $orderData['AP_PURCHASE_COUPONNAME'] = isset($ruleData['name']) ? $ruleData['name'] :'';
        $orderData['AP_PURCHASE_COUPONNO'] = isset($ruleData['coupon']) ? $ruleData['coupon'] :'';
        $orderData['AP_PURCHASE_TAX'] = $order->getTaxAmount();
        $orderData['AP_PURCHASE_TYPE'] = $order->getPayment()->getMethod();
        return $orderData;
    }

    /**
     * Get Parent Product of Bundle or Configurable in order
     *
     * @param $allItems
     * @return array
     */
    private function getOrderRealParentItemsData($allItems)
    {
        $products = [];
        $allItemsArr = [];
        $parentSku = '';
        foreach ($allItems as $item) {
            $allItemsArr[] = $item->getData();
        }
        foreach ($allItems as $item) {
            $product = [];
            if ($item->getParentItemId()) {
                $parentItem = $allItems[array_search($item->getParentItemId(), array_column($allItemsArr, 'item_id'))];
                if($parentSku == $parentItem->getSku()) {
                    continue;
                }
                $parentProduct = $parentItem->getProduct();
                $product['name'] = $parentProduct->getData('name');
                $product['code'] = $parentProduct->getData('sku');
                $product['apg_brand_code'] = substr($parentProduct->getData('sku'), 0, 5);
                $product['sapcode'] = $parentProduct->getData('sku');
                $product['brand'] = $this->helper->getSiteName() ?? '';
                $product['quantity'] = intval($parentItem->getQtyOrdered());
                $product['variant'] = '';
                $product['promotion'] = '';
                $product['cate'] = $this->helper->getProductCategory($parentProduct);
                $product['catecode'] = '';
                $product['url'] = $parentItem->getProduct()->getProductUrl();
                $product['img_url'] = $this->catalogProductHelper->getThumbnailUrl($parentItem->getProduct());
                if ($parentItem->getProductType() === 'bundle') {
                    $product['prdprice'] = intval($parentProduct->getPriceInfo()->getPrice('regular_price')->getMinimalPrice()->getValue()) ;
                    $product['price'] = intval($parentProduct->getPriceInfo()->getPrice('final_price')->getMinimalPrice()->getValue());

                }

                if ($parentItem->getAppliedRuleIds()) {
                    $ruleData =  $this->getRuleName($parentItem->getAppliedRuleIds());
                    $product['promotion'] = $ruleData['name'];
                    $product['promotion_code'] = $ruleData['coupon'];
                }

                if ($parentItem->getProductType() === 'configurable') {
                    $product['price'] =  intval($parentItem->getPrice()); // cat rule applied
                    $product['prdprice'] = intval($parentProduct->getPriceInfo()->getPrice('regular_price')->getValue()) ?? 0;
                    // $product['price'] = intval($parentProduct->getPriceInfo()->getPrice('final_price')->getValue()) ?? 0;
                    $nameArr = explode(' ', $item->getName());
                    $product['variant'] = $nameArr[(count($nameArr) - 1)];
                }
                $product['discount'] = $product['prdprice'] - $product['price'];
                $products[] = $product;
                $parentSku = $parentItem->getSku();
            }
            else {
                if ($item->getProductType() == 'simple') {
                    $product['name'] = $item->getName();
                    $product['code'] = $item->getSku();
                    $product['apg_brand_code'] = substr($item->getData('sku'), 0, 5);
                    $product['sapcode'] = $item->getSku();
                    $product['brand'] = $this->helper->getSiteName() ?? '';
                    $product['prdprice'] = $item->getPrice();
                    $product['discount'] = $item->getDiscountAmount();
                    $product['price'] =  ($product['prdprice'] *  intval($item->getQty())) - $product['discount'];
                    $product['quantity'] = intval($item->getQtyOrdered());
                    $product['variant'] =  '';
                    $ruleData = $this->getRuleName($item->getAppliedRuleIds());
                    $product['promotion'] = $ruleData['name'];
                    $product['promotion_code'] = $ruleData['coupon'];
                    $product['cate'] = $this->helper->getProductCategory($item->getProduct());
                    $product['catecode'] = '';
                    $product['url'] = $item->getProduct()->getProductUrl();
                    $product['img_url'] = $this->catalogProductHelper->getThumbnailUrl($item->getProduct());
                    $products[] = $product;
                }
            }

        }
        return $products;

    }

    protected function getProductImage($productId)
    {
        $product = $this->productRepository->getById($productId);
        return $this->catalogProductHelper->getThumbnailUrl($product);
    }

    private function getCheckoutSession()
    {
        if (!$this->checkoutSession->isSessionExists()) {
            $this->checkoutSession->start();
        }
        return $this->checkoutSession;
    }

    public function getCanceledOrderData()
    {
        $hasRefunded = $this->getRequest()->getParam('refund');
        if (!$hasRefunded) {
            return false;
        }
        $orderId = $this->getRequest()->getParam('order_id');
        /** @var \Magento\Sales\Api\Data\OrderInterface $order */
        $order = $this->orderRepository->get($orderId);
        if (!$order) {
            return false;
        }
        $canceledOrderData = [
            'AP_REFUND_PRICE' => 0,
            'AP_REFUND_ORDERNUM' => 0,
            'AP_REFUND_CONTENT' => 0,
            'AP_REFUND_PRDS' => []
        ];
        $allItems = $order->getAllItems();
        if (count($allItems) < 1) {
            return false;
        }
        $realItemsData = $this->getOrderRealItemsData($allItems);
        $orderProdPrice = 0;
        foreach ($realItemsData as $item) {
            $orderProdPrice += $item['prdprice'] * $item['quantity'];
            $canceledOrderData['AP_REFUND_PRDS'][] = $this->jsonSerializer->serialize($item);
        }
        $canceledOrderData['AP_REFUND_PRICE'] = intval($order->getTotalRefunded());
        $canceledOrderData['AP_REFUND_ORDERNUM'] = intval($order->getGrandTotal());
        $canceledOrderData['AP_REFUND_CONTENT'] = '단순변심';
        return $canceledOrderData;
    }

    /**
     * @return string
     */
    public function getCurrentCurrencyCode()
    {
        return $this->_storeManager->getStore()->getCurrentCurrency()->getCode();
    }

    /**
     * @param $product
     * @return float|mixed|null
     */
    public function getProductDefaultQty($product = null)
    {
        $qty = $this->getMinimalQty($product);
        $config = $product->getPreconfiguredValues();
        $configQty = $config->getQty();
        if ($configQty > $qty) {
            $qty = $configQty;
        }

        return $qty;
    }

    /**
     * @param $product
     * @return float|null
     */
    public function getMinimalQty($product)
    {
        $stockItem = $this->stockRegistry->getStockItem($product->getId(), $product->getStore()->getWebsiteId());
        $minSaleQty = $stockItem->getMinSaleQty();
        return $minSaleQty > 0 ? $minSaleQty : null;
    }

    public function getCurrentDate() {
        return $this->dateTimeFactory->create()->gmtDate(self::FORMAT_DATE);
    }

    /**
     * @param $product
     * @return mixed
     */
    public function getProductOriginalPrice($product)
    {
        return $this->ap->getProductOriginalPrice($product);
    }

    /**
     * @param $product
     * @return float|mixed
     */
    public function getProductDiscountedPrice($product)
    {
        return $this->ap->getProductDiscountedPrice($product);
    }

    /**
     * @return \Magento\Customer\Model\Customer
     */
    public function getCurrentCustomer()
    {
        return $this->customerSession->getCustomer();
    }

    /**
     * @return mixed
     */
    public function getEventRegisterSuccess() {
        return $this->customerSession->getEventRegisterSuccess();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setEventRegisterSuccess($value) {
        return $this->customerSession->setEventRegisterSuccess($value);
    }

    public function getEventSocialLoginSuccess() {
        return $this->customerSession->getEventSocialLoginSuccess();
    }

    /**
     * @param $value
     * @return mixed
     */
    public function setEventSocialLoginSuccess($value) {
        return $this->customerSession->setEventSocialLoginSuccess($value);
    }

    /**
     *
     * Get social login type
     * @return mixed|string
     */
    public function getSocialTypeLogin() {
        $coreSession = $this->socialLoginModel->getCoreSession();
        $loginType = 'General Login';
        if (isset($coreSession->getData()['socialmedia_type'])) {
            $loginType = $coreSession->getData()['socialmedia_type'];
        }
        return $loginType;
    }
    /**
     * @param $customer
     * @return string
     */
    public function getCustomerIntegrationNumber($customer) {
        try {
            $integrationNumber = $customer->getIntegrationNumber();
            return $integrationNumber ? hash('sha512', $customer->getIntegrationNumber()) : 'X';
        } catch (\Exception $e) {
            return '';
        }
    }

    /**
     * @param $customer
     * @return false|string
     */
    public function getCustomerRegisterDate($customer) {
        $registerDate = $customer->getCreatedAt();
        return $this->dateTimeFactory->create()->gmtDate(self::FORMAT_DATE, $registerDate);
    }

    /**
     * @param $ruleIds
     * @return array
     */
    public function getRuleName($ruleIds = '') {
        if (!$ruleIds) {
            return [];
        }
        $ruleName = [];
        $couponCode = [];
        $collection = $this->ruleCollectionFactory->create();
        $collection->addFieldToFilter('rule_id', ['in' => $ruleIds]);
        foreach($collection as $rule) {
            $ruleName[] = $rule->getName();
            $couponCode[] = $rule->getCode();
        }
        $ruleData = [
            'name' =>  implode('|', $ruleName),
            'coupon' => implode('|', $couponCode)
        ];
        return $ruleData;
    }
}

