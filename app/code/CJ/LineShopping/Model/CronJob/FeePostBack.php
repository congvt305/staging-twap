<?php

namespace CJ\LineShopping\Model\CronJob;

use CJ\LineShopping\Model\Rest\Api as LineShoppingApi;
use Magento\Sales\Model\Order;
use CJ\LineShopping\Helper\Data as DataHelper;
use CJ\LineShopping\Helper\Config;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;
use CJ\LineShopping\Logger\Logger;

class FeePostBack
{
    /**
     * @var Config
     */
    protected Config $config;

    /**
     * @var LineShoppingApi
     */
    protected LineShoppingApi $lineShoppingApi;

    /**
     * @var OrderCollectionFactory
     */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * @var DataHelper
     */
    protected DataHelper $dataHelper;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param OrderCollectionFactory $orderCollectionFactory
     * @param LineShoppingApi $lineShoppingApi
     * @param DataHelper $dataHelper
     * @param Config $config
     */
    public function __construct(
        Logger $logger,
        OrderCollectionFactory $orderCollectionFactory,
        LineShoppingApi $lineShoppingApi,
        DataHelper $dataHelper,
        Config $config
    ) {
        $this->logger = $logger;
        $this->orderCollectionFactory = $orderCollectionFactory;
        $this->lineShoppingApi = $lineShoppingApi;
        $this->dataHelper = $dataHelper;
        $this->config = $config;
    }

    /**
     * Execute fee post back
     */
    public function execute()
    {
        /** @var OrderCollectionFactory $orderColelction */
        $orderColelction = $this->orderCollectionFactory->create()
            ->addFieldToSelect('*')
            ->addFieldToFilter('is_line_shopping', 1)
            ->addFieldToFilter(DataHelper::IS_SENT_FEE_POST_BACK, 0);
        $trialPeriod = $this->config->getTrialPeriod();

        /** @var Order $order */
        foreach ($orderColelction as $order) {
            try {
                $current = new \DateTime(date(DataHelper::TIME_FORMAT_YMDHIS));
                $orderCreatedAt = new \DateTime($order->getCreatedAt());
                $interval = $current->diff($orderCreatedAt);
                if ($interval->days >= $trialPeriod && $this->dataHelper->verifyOrderSendToLine($order)) {
                    $result = $this->lineShoppingApi->feePostBack($order);
                    if ($result == LineShoppingApi::LINE_SHOPPING_SUCCESS_MESSAGE) {
                        $this->dataHelper->updateOrderHistory($result, $order, 'fee');
                        $this->dataHelper->updateOrderData($order, DataHelper::IS_SENT_FEE_POST_BACK, 1);
                    }
                }
            } catch (\Exception $exception) {
                $this->logger->addError(Logger::FEE_POST_BACK,
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
