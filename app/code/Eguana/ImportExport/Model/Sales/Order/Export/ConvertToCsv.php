<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 30/11/20
 * Time: 5:11 PM
 */
namespace Eguana\ImportExport\Model\Sales\Order\Export;

use Amore\CustomerRegistration\Helper\Data;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Model\ProductRepository;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\Exception\FileSystemException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Filesystem;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Sales\Model\Order;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory as OrderCollectionFactory;
use Magento\Sales\Model\ResourceModel\Order\Item\CollectionFactory as ItemsCollectionFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Model class to export data in csv format
 *
 * Class ConvertToCsv
 */
class ConvertToCsv
{
    /**
     * @var int|null
     */
    private $loadedProductId = 0;

    /**
     * @var string
     */
    private $loadedSku = '';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var OrderCollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @var ItemsCollectionFactory
     */
    private $itemsCollectionFactory;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Data
     */
    private $customerRegistrationHelper;

    /**
     * @param Data $customerRegistrationHelper
     * @param Filter $filter
     * @param Filesystem $filesystem
     * @param LoggerInterface $logger
     * @param ProductRepository $productRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param ItemsCollectionFactory $itemsCollectionFactory
     * @param OrderRepositoryInterface $orderRepository
     */
    public function __construct(
        Data $customerRegistrationHelper,
        Filter $filter,
        Filesystem $filesystem,
        LoggerInterface $logger,
        ProductRepository $productRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        OrderCollectionFactory $orderCollectionFactory,
        ItemsCollectionFactory $itemsCollectionFactory,
        OrderRepositoryInterface $orderRepository
    ) {
        $this->filter = $filter;
        $this->logger = $logger;
        $this->orderRepository = $orderRepository;
        $this->productRepository = $productRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->itemsCollectionFactory = $itemsCollectionFactory;
        $this->customerRegistrationHelper = $customerRegistrationHelper;
    }

    /**
     * Returns CSV file
     *
     * @return array
     * @throws FileSystemException
     * @throws LocalizedException
     */
    public function getCsvFile()
    {
        $collection = $this->filter->getCollection($this->orderCollectionFactory->create());
        $collection->getSelect()->joinRight(
            ['so' => $collection->getTable('sales_order')],
            'so.entity_id = main_table.entity_id',
            ['shipping_amount', 'delivery_message', 'grand_total', 'customer_ba_code']
        );

        $name = microtime();
        $file = 'export/items-report-' . $name . '.csv';
        $this->directory->create('export');

        $stream = $this->directory->openFile($file, 'w+');
        $stream->lock();

        $columns = [
            'Order',
            'Order Status',
            'Customer (Receiver)',
            'Order Mobile',
            'Address',
            'SKU',
            'Parent SKU',
            'Product Type',
            'Product Name',
            'Quantity',
            'Original Price',
            'Discount Amount',
            'Shipping Fee',
            'Grand Total',
            'Delivery Message'
        ];

        if ($this->customerRegistrationHelper->getBaCodeEnable()) {
            $columns[] = 'BA Recruiter Code';
        }

        $header = [];
        foreach ($columns as $column) {
            if ($column == 'Order Mobile') {
                $header[] = __($column);
            } else {
                $header[] = $column;
            }
        }

        $stream->writeCsv($header);

        $orders     = [];
        $orderIds   = [];
        foreach ($collection->getItems() as $item) {
            $this->searchCriteriaBuilder->addFilter('increment_id', $item->getIncrementId());

            /** @var Order $order */
            $orderList = $this->orderRepository
                ->getList($this->searchCriteriaBuilder->create())
                ->getItems();
            $order = current($orderList);
            $mobile = $order->getShippingAddress() ? $order->getShippingAddress()->getTelephone() : '';

            $orderIds[] = $item->getId();
            $orders[$item->getId()]['order_id'] = $item->getIncrementId();
            $orders[$item->getId()]['order_status'] = $order->getStatusLabel();
            $orders[$item->getId()]['customer'] = $item->getCustomerName();
            $orders[$item->getId()]['address'] = $item->getShippingAddress();
            $orders[$item->getId()]['shipping_amount'] = $item->getShippingAmount();
            $orders[$item->getId()]['delivery_message'] = $item->getDeliveryMessage();
            $orders[$item->getId()]['grand_total'] = $item->getGrandTotal();
            $orders[$item->getId()]['mobile'] = $mobile;
            $orders[$item->getId()]['ba_code'] = $item->getCustomerBaCode();
        }

        $itemsCollection = $this->itemsCollectionFactory->create();
        $itemsCollection->addFieldToFilter('order_id', ['in' => $orderIds]);
        $items = $itemsCollection->getItems();

        $orderItems = [];
        foreach ($items as $item) {
            $itemData = [];
            $itemData[] = $orders[$item->getOrderId()]['order_id'];
            $itemData[] = $orders[$item->getOrderId()]['order_status'];
            $itemData[] = $orders[$item->getOrderId()]['customer'];
            $itemData[] = $orders[$item->getOrderId()]['mobile'];
            $itemData[] = $orders[$item->getOrderId()]['address'];
            if ($item->getParentItemId()) {
                $parentItem = $itemsCollection->getItemById($item->getParentItemId());
                $parentSku = $this->getProductSku($parentItem->getProductId());
                $itemData[] = $item->getSku();
                $itemData[] = $parentSku;
            } else {
                $sku = $this->getProductSku($item->getProductId());
                $itemData[] = $sku;
                $itemData[] = '';
            }
            $itemData[] = $this->getProductType($item->getProductType());
            $itemData[] = $item->getName();
            $itemData[] = (int) $item->getQtyOrdered();
            $itemData[] = $item->getOriginalPrice();
            $itemData[] = $item->getDiscountAmount();
            $itemData[] = $orders[$item->getOrderId()]['shipping_amount'];
            $itemData[] = $orders[$item->getOrderId()]['grand_total'];
            $itemData[] = $orders[$item->getOrderId()]['delivery_message'];
            if ($this->customerRegistrationHelper->getBaCodeEnable()) {
                $itemData[] = $orders[$item->getOrderId()]['ba_code'];
            }

            $stream->writeCsv($itemData);
        }

        $stream->unlock();
        $stream->close();

        return [
            'type' => 'filename',
            'value' => $file,
            'rm' => true
        ];
    }

    /**
     * Return product type
     *
     * @param $type
     * @return string
     */
    private function getProductType($type)
    {
        $productType = '';
        if ($type == 'simple') {
            $productType = 'Simple Product';
        } elseif ($type == 'configurable') {
            $productType = 'Configurable Product';
        } elseif ($type == 'bundle') {
            $productType = 'Bundle Product';
        } elseif ($type == 'grouped') {
            $productType = 'Grouped Product';
        } elseif ($type == 'virtual') {
            $productType = 'Virtual Product';
        }
        return $productType;
    }

    /**
     * Get product data by id
     *
     * @param $id
     * @return array|ProductInterface|mixed|void
     */
    private function getProduct($id)
    {
        try {
            return $this->productRepository->getById($id);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            return [];
        }
    }

    /**
     * Get product sku
     *
     * @param $id
     * @return string
     */
    private function getProductSku($id)
    {
        if ($id == $this->loadedProductId) {
            return $this->loadedSku;
        } else {
            $this->loadedProductId = $id;
            $product = $this->getProduct($id);
            $this->loadedSku = $product ? $product->getSku() : '';
            return $this->loadedSku;
        }
    }
}
