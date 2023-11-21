<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/20
 * Time: 1:12 PM
 */

namespace Eguana\EInvoice\Model;

use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Sales\Api\Data\OrderSearchResultInterface;
use Eguana\EInvoice\Model\Source\Config;

class Order
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Sales\Model\ResourceModel\Order\CollectionFactory
     */
    private $orderCollectionFactory;

    /**
     * @param DateTime $dateTime
     * @param Config $config
     * @param \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        DateTime $dateTime,
        Config $config,
        \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory
    ) {
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    /**
     * Get complete order to check whether if the order has einvoice or not
     *
     * @param $storeId
     * @return OrderSearchResultInterface
     */
    public function getCompletedOrders($storeId)
    {
        $daysLimit = $this->config->getDaysLimit($storeId);
        $dateFrom = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -'.$daysLimit.'days'));

        $collection = $this->orderCollectionFactory->create();
        $collection->addFieldToFilter('store_id', $storeId)
            ->addFieldToFilter('created_at', ['gteq' => $dateFrom])
            ->addFieldToFilter('status', ['in' => ['shipment_processing', 'complete', 'delivery_complete']])
            ->join(
                ['sop' => 'sales_order_payment'],
                'main_table.entity_id = sop.parent_id',
                ['additional_data', 'amount_paid']
            );
        return $collection;
    }

    /**
     * @param $storeId
     * @return array
     */
    public function getNotIssuedOrders($storeId)
    {
        $orderList = $this->getCompletedOrders($storeId);

        $notIssuedOrderList = [];
        foreach ($orderList->getItems() as $order) {
            if (!$order->getAmountPaid()) {
                continue;
            }
            $eInvoiceData = json_decode($order->getAdditionalData()??'', true);

            if (empty($eInvoiceData) || (isset($eInvoiceData["RtnCode"]) && $eInvoiceData["RtnCode"] != 1)) {
                $notIssuedOrderList[] = $order;
            }
        }

        return $notIssuedOrderList;
    }
}
