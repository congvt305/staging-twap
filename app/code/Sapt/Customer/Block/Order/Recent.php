<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sapt\Customer\Block\Order;

use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session;
use Magento\Sales\Model\Order\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Sapt\Customer\Model\ResourceModel\Order as OrderResourceModel;
use \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory as RmaCollectionFactory;

/**
 * Sales order history block
 *
 * @api
 * @since 100.0.2
 */
class Recent extends \Magento\Framework\View\Element\Template
{
    const STATUS_DELIVERY_COMPLETE = 'delivery_complete';
    const STATUS_CODE_PROCESSING_WITH_SHIPMENT = 'processing_with_shipment';
    const STATUS_SAP_SUCCESS = 'sap_success';
    const STATUS_SHIPMENT_PROCESSING = 'shipment_processing';
    const STATUS_PREPARING = 'preparing';
    const STATUS_COMPLETE = 'complete';
    const STATUS_RMA_AUTH = 'authorized';

    /**
     * Limit of orders
     */
    const ORDER_LIMIT = 2;

    /**
     * @var CollectionFactoryInterface
     */
    protected $_orderCollectionFactory;

    /**
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $_orderConfig;

    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    /**
     * @var \Magento\Checkout\Helper\Cart
     */
    protected $_cartHelper;

    /**
     * @var OrderResourceModel
     */
    private OrderResourceModel $orderResource;

    /**
     * Rma grid collection
     *
     * @var \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param CollectionFactoryInterface $orderCollectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param \Magento\Sales\Model\Order\Config $orderConfig
     * @param \Magento\Catalog\Api\ProductRepositoryInterface $productRepository
     * @param \Magento\Checkout\Helper\Cart $_cartHelper
     * @param OrderResourceModel $orderResource
     * @param RmaCollectionFactory $_collectionFactory
     * @param array $data
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     */

    public function __construct(
        Context $context,
        CollectionFactoryInterface $orderCollectionFactory,
        Session $customerSession,
        Config $orderConfig,
        ProductRepositoryInterface $productRepository,
        \Magento\Checkout\Helper\Cart $_cartHelper,
        OrderResourceModel $orderResource,
        RmaCollectionFactory $collectionFactory,
        array $data = [],
        StoreManagerInterface $storeManager = null
    ) {
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->_customerSession = $customerSession;
        $this->_orderConfig = $orderConfig;
        $this->_isScopePrivate = true;
        $this->storeManager = $storeManager ?: ObjectManager::getInstance()
            ->get(StoreManagerInterface::class);
        $this->productRepository = $productRepository;
        $this->_cartHelper = $_cartHelper;
        $this->orderResource = $orderResource;
        $this->_collectionFactory = $collectionFactory;
        parent::__construct($context, $data);
    }

    /**
     * @inheritDoc
     */
    protected function _construct()
    {
        parent::_construct();
        $this->getRecentOrders();
    }

    /**
     * Get recently placed orders. By default they will be limited by 5.
     */
    private function getRecentOrders()
    {
        $customerId = $this->_customerSession->getCustomerId();
        $orders = $this->_orderCollectionFactory->create($customerId)->addAttributeToSelect(
            '*'
        )->addAttributeToFilter(
            'customer_id',
            $customerId
        )->addAttributeToFilter(
            'store_id',
            $this->storeManager->getStore()->getId()
        )->addAttributeToFilter(
            'status',
            ['in' => $this->_orderConfig->getVisibleOnFrontStatuses()]
        )->addAttributeToSort(
            'created_at',
            'desc'
        )->setPageSize(
            self::ORDER_LIMIT
        )->load();
        $this->setOrders($orders);
    }

    /**
     * Get order view URL
     *
     * @param object $order
     * @return string
     */
    public function getViewUrl($order)
    {
        return $this->getUrl('sales/order/view', ['order_id' => $order->getId()]);
    }

    /**
     * Get order track URL
     *
     * @param object $order
     * @return string
     * @deprecated 102.0.3 Action does not exist
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function getTrackUrl($order)
    {
        //phpcs:ignore Magento2.Functions.DiscouragedFunction
        trigger_error('Method is deprecated', E_USER_DEPRECATED);
        return '';
    }

    /**
     * @inheritDoc
     */
    protected function _toHtml()
    {
        if ($this->getOrders()->getSize() > 0) {
            return parent::_toHtml();
        }
        return '';
    }

    /**
     * Get reorder URL
     *
     * @param object $order
     * @return string
     */
    public function getReorderUrl($order)
    {
        return $this->getUrl('sales/order/reorder', ['order_id' => $order->getId()]);
    }

    public function getRmaCount() {
        $returnCnt = $this->_collectionFactory->create()->addFieldToSelect(
            'order_id'
        )->addFieldToFilter(
            'customer_id',
            $this->_customerSession->getCustomer()->getId()
        )->addFieldToFilter(
            'status',
            self::STATUS_RMA_AUTH
        )->getSize();

        return $returnCnt;
    }

    public function getDeliveryStatusCount() {
        $customerId = $this->_customerSession->getCustomerId();
        $data = [
            'processing' => 0,
            'preparing' => 0,
            'in_delivery' => 0,
            'delivered' => 0,
            'refund' => 0
        ];

        try {
            if (!empty($customerId)) {
                $data = [
                    'processing' => $this->orderResource->getTotalOrders(self::STATUS_CODE_PROCESSING_WITH_SHIPMENT, $customerId, null),
                    'preparing' => $this->orderResource->getTotalOrders(self::STATUS_SAP_SUCCESS, $customerId,self::STATUS_PREPARING),
                    'in_delivery' => $this->orderResource->getTotalOrders(self::STATUS_SHIPMENT_PROCESSING, $customerId, null),
                    'delivered' => $this->orderResource->getTotalOrders(self::STATUS_DELIVERY_COMPLETE, $customerId,self::STATUS_COMPLETE),
                    'refund' => $this->getRmaCount()
                ];
            }
        } catch (\Throwable $e) {}

        return $data;
    }

    public function formatOrderDate($date) {
        return date('Y.m.d', strtotime($date));
    }

    /**
     * Get product
     *
     * @param \Magento\Sales\Model\Order\Item
     * @return string
     */
    public function getProduct($orderItem)
    {
        $id = $orderItem->getProductId();
        $product = $this->productRepository->getById($id);

        return $product;
    }

    /**
     * Get order item thumbnail
     *
     * @param \Magento\Sales\Model\Order\Item
     * @return string
     */
    public function getOrderItemThumbnail($orderItem)
    {
        $id = $orderItem->getProductId();
        $product = $this->productRepository->getById($id);

        return $this->getThumbnailUrl($product);
    }

    /**
     * Get order item url
     *
     * @param \Magento\Sales\Model\Order\Item
     * @return string
     */
    public function getOrderItemUrl($orderItem)
    {
        $id = $orderItem->getProductId();
        $product = $this->productRepository->getById($id);

        return $this->getProductUrl($product);
    }

    /**
     * Retrieve thumbnail image url
     *
     * @param \Magento\Catalog\Model\Product|\Magento\Framework\DataObject $product
     * @return string|bool
     */
    public function getThumbnailUrl($product)
    {
        $url = false;
        $attribute = $product->getResource()->getAttribute('thumbnail');
        if (!$product->getThumbnail()) {
            $url = $this->_assetRepo->getUrl('Magento_Catalog::images/product/placeholder/thumbnail.jpg');
        } elseif ($attribute) {
            $url = $attribute->getFrontend()->getUrl($product);
        }
        return $url;
    }

    /**
     * Retrieve url for add product to cart
     *
     * Will return product view page URL if product has required options
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional
     * @return string
     */
    public function getAddToCartUrl($product, $additional = [])
    {
        if (!$product->getTypeInstance()->isPossibleBuyFromList($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            if (!isset($additional['_query'])) {
                $additional['_query'] = [];
            }
            $additional['_query']['options'] = 'cart';

            return $this->getProductUrl($product, $additional);
        }
        return $this->_cartHelper->getAddUrl($product, $additional);
    }

    /**
     * Retrieve Product URL using UrlDataObject
     *
     * @param \Magento\Catalog\Model\Product $product
     * @param array $additional the route params
     * @return string
     */
    public function getProductUrl($product, $additional = [])
    {
        if ($this->hasProductUrl($product)) {
            if (!isset($additional['_escape'])) {
                $additional['_escape'] = true;
            }
            return $product->getUrlModel()->getUrl($product, $additional);
        }

        return '#';
    }

    /**
     * Check Product has URL
     *
     * @param \Magento\Catalog\Model\Product $product
     * @return bool
     */
    public function hasProductUrl($product)
    {
        if ($product->getVisibleInSiteVisibilities()) {
            return true;
        }
        if ($product->hasUrlDataObject()) {
            if (in_array($product->hasUrlDataObject()->getVisibility(), $product->getVisibleInSiteVisibilities())) {
                return true;
            }
        }

        return false;
    }
}
