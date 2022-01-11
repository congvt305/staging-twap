<?php

namespace CJ\LineShopping\Observer;

use CJ\LineShopping\Model\Rest\Api as LineShoppingApi;
use CJ\LineShopping\Helper\Config;
use CJ\LineShopping\Helper\Data as DataHelper;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Sales\Api\Data\OrderInterface;
use Exception;
use CJ\LineShopping\Logger\Logger;

class PushDataToLine implements ObserverInterface
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
     * @var Config
     */
    protected Config $config;

    /**
     * @var Logger
     */
    protected Logger $logger;

    /**
     * @param Logger $logger
     * @param LineShoppingApi $lineShoppingApi
     * @param Config $config
     * @param DataHelper $dataHelper
     */
    public function __construct(
        Logger $logger,
        LineShoppingApi $lineShoppingApi,
        Config $config,
        DataHelper $dataHelper
    ) {
        $this->logger = $logger;
        $this->lineShoppingApi = $lineShoppingApi;
        $this->config = $config;
        $this->dataHelper = $dataHelper;
    }
    /**
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        try {
            /** @var OrderInterface $order */
            $order = $observer->getData('order');
            if (!$order->getData('line_ecid') || !$this->config->isEnable($order->getStore()->getWebsiteId())) {
                return $this;
            }
            $result = $this->lineShoppingApi->orderPostBack($order);
            //if ($result == LineShoppingApi::LINE_SHOPPING_SUCCESS_MESSAGE) {
                $this->dataHelper->updateOrderHistory($result, $order, 'order');
                $this->dataHelper->updateOrderData($order, 'is_send_order_post_back', 1);
            //}
        } catch (Exception $exception) {
            $this->logger->error(Logger::ORDER_POST_BACK,
                [
                    'type' => 'Send order to Line',
                    'message' => $exception->getMessage()
                ]
            );
        }
    }
}
