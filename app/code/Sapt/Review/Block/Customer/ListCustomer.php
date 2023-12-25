<?php
/**
 * Copyright ©  All rights reserved.
 * See COPYING.txt for license details.
 */
declare(strict_types=1);

namespace Sapt\Review\Block\Customer;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ObjectManager;
use Magento\Review\Helper\Data as ReviewHelper;

class ListCustomer extends \Magento\Review\Block\Customer\ListCustomer
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    protected $orderCollectionFactory;

    /**
     * @var \Magento\Sales\Model\Order\Config
     */
    protected $orderConfig;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    protected $orders;

    /**
     * @var \Magento\Catalog\Api\ProductRepositoryInterface
     */
    protected $productRepository;

    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Customer\Model\Session $customerSession,
        \Magento\Newsletter\Model\SubscriberFactory $subscriberFactory,
        CustomerRepositoryInterface $customerRepository,
        AccountManagementInterface $customerAccountManagement,
        \Magento\Review\Model\ResourceModel\Review\Product\CollectionFactory $collectionFactory,
        \Magento\Customer\Helper\Session\CurrentCustomer $currentCustomer,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
        \Magento\Sales\Model\Order\Config $orderConfig,
        \Magento\Catalog\Api\ProductRepositoryInterface $productRepository,
        array $data = [],
        ?ReviewHelper $reviewHelper = null
    ) {
        $data['reviewHelper'] = $reviewHelper ?? ObjectManager::getInstance()->get(ReviewHelper::class);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->orderConfig = $orderConfig;
        $this->productRepository = $productRepository;
        parent::__construct(
            $context,
            $customerSession,
            $subscriberFactory,
            $customerRepository,
            $customerAccountManagement,
            $collectionFactory,
            $currentCustomer,
            $data
        );
    }

    /**
     * Get reviews
     *
     * @return bool|\Magento\Review\Model\ResourceModel\Review\Product\Collection
     */
    public function getReviews()
    {
        if ($this->_storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return parent::getReviews();
        }

        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->_collection) {
            $this->_collection = $this->_collectionFactory->create()->addAttributeToSelect('thumbnail');
            $this->_collection
                ->addStoreFilter($this->_storeManager->getStore()->getId())
                ->addCustomerFilter($customerId)
                ->setDateOrder();
        }
        return $this->_collection;
    }

    /**
     * Get unwritten reviews
     *
     * @return bool|\Magento\Review\Model\ResourceModel\Review\Product\Collection
     */
    public function getUnwrittenReviews()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }

        // Get all orders of current customer
        $orders = $this->getOrders();

        $orderItems = [];
        foreach($orders as $order) {
            foreach($order->getAllVisibleItems() as $orderItem) {
                if(!$orderItem->getParentItemId()) {
                    $orderItems[] = $orderItem;
                }
            }
        }

        return $orderItems;
    }

    /**
     * Get Add Review Form
     *
     * @param \Magento\Sales\Model\Order\Item
     * @return string
     */
    public function getAddReviewForm($orderItem)
    {
        $component = ['components' => ['review-form' => ['component' => 'Magento_Review/js/view/review']]];

        $id = $orderItem->getProductId();
        $product = $this->productRepository->getById($id);

        return $this->getLayout()->createBlock(\Amasty\AdvancedReview\Block\Widget\ProductReviews\Form::class)
            ->setTemplate('Magento_Review::form_mypage.phtml')
            ->setData('jsLayout', $component)
            ->setProduct($product)
            ->setData('order_item_id',$orderItem->getId())
            ->toHtml();
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

        return $product->getUrlModel()->getUrl($product, ['_escape' => true]);
    }


    /**
     * Get review writing period
     *
     * @param \Magento\Sales\Model\Order\Item
     * @return string
     */
    public function getReviewWritingPeriod($orderItem)
    {
        $createdDate = $orderItem->getCreatedAt();

        return date('Y.m.d', strtotime($createdDate. ' + 31 days'));
    }

    /**
     * Get customer orders
     *
     * @return bool|\Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getOrders()
    {
        if (!($customerId = $this->currentCustomer->getCustomerId())) {
            return false;
        }
        if (!$this->orders) {
            $this->orders = $this->orderCollectionFactory->create($customerId)->addFieldToSelect(
                '*'
            )->addFieldToFilter(
                'status',
                ['in' => $this->orderConfig->getVisibleOnFrontStatuses()]
            )->setOrder(
                'created_at',
                'desc'
            );
        }
        return $this->orders;
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
     * Get formatted date
     *
     * @param string $date
     * @return string
     */
    public function dateFormat($date)
    {
        if ($this->_storeManager->getStore()->getCode() != self::MY_SWS_STORE_CODE) {
            return parent::dateFormat($date);
        }

        return $this->formatDate($date, \IntlDateFormatter::LONG);
    }
}
