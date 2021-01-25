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

use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class Order
{
    /**
     * @var CollectionFactory
     */
    private $ordercollectionFactory;

    /**
     * Order constructor.
     * @param CollectionFactory $ordercollectionFactory
     */
    public function __construct(
        CollectionFactory $ordercollectionFactory
    ) {
        $this->ordercollectionFactory = $ordercollectionFactory;
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    public function getCompletedOrders()
    {
        return $this->ordercollectionFactory->create()
            ->addAttributeToSelect("*")
            ->addAttributeToFilter("status", ["in" => ["shipment_processing", "processing"]]);
    }

    /**
     * @return array
     */
    public function getNotIssuedOrders()
    {
        $orderList = $this->getCompletedOrders();

        $notIssuedOrderList = [];
        foreach ($orderList->getItems() as $order) {
            $payment = $order->getPayment();
            $eInvoiceData = json_decode($payment->getAdditionalData(), true);

            if (empty($eInvoiceData) || $eInvoiceData["RtnCode"] != 1) {
                $notIssuedOrderList[] = $order;
            }
        }

        return $notIssuedOrderList;
    }
}
