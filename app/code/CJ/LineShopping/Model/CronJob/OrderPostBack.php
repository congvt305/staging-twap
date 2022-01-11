<?php

namespace CJ\LineShopping\Model\CronJob;

use CJ\LineShopping\Logger\Logger;
use CJ\LineShopping\Model\Rest\Api as LineShoppingApi;
use Magento\Sales\Model\Order;
use CJ\LineShopping\Helper\Data as DataHelper;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class OrderPostBack
{
    /**
     * @var LineShoppingApi
     */
    protected LineShoppingApi $lineShoppingApi;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @var OrderCollectionFactory
     */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param LineShoppingApi $lineShoppingApi
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Logger $logger,
        OrderCollectionFactory $orderCollectionFactory,
        LineShoppingApi $lineShoppingApi,
        DataHelper $dataHelper
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->lineShoppingApi = $lineShoppingApi;
        $this->dataHelper = $dataHelper;
    }

    /**
     * Execute order post back
     */
    public function execute()
    {
        /** @var OrderCollectionFactory $orderColelction */
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_line_shopping', 1)
            ->addFieldToFilter('is_send_order_post_back', 0);

        /** @var Order $order */
        foreach ($orderCollection as $order) {
            try {
                if($this->dataHelper->verifyOrderSendToLine($order)) {
                    $result = $this->lineShoppingApi->orderPostBack($order);
                    if ($result == LineShoppingApi::LINE_SHOPPING_SUCCESS_MESSAGE) {
                        $this->dataHelper->updateOrderHistory($result, $order, 'order');
                        $this->dataHelper->updateOrderData($order, 'is_send_order_post_back', 1);
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->addError(Logger::ORDER_POST_BACK,
                    [
                        'orderId' => $order->getId(),
                        'message' => $exception->getMessage()
                    ]
                );
                continue;
            }
        }
    }
}
