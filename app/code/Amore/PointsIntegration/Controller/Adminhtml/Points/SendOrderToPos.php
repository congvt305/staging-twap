<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-04
 * Time: 오후 3:44
 */

namespace Amore\PointsIntegration\Controller\Adminhtml\Points;

use Amore\PointsIntegration\Logger\Logger;
use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Store\Model\StoreManagerInterface;

class SendOrderToPos extends Action
{
    /**
     * @var \Amore\PointsIntegration\Model\GetOrdersToSendPos
     */
    private $ordersToSendPos;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var JsonFactory
     */
    private $jsonFactory;
    /**
     * @var \Amore\PointsIntegration\Model\PosOrderSender
     */
    private $posOrderSender;
    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * SendOrderToPos constructor.
     * @param Action\Context $context
     * @param \Amore\PointsIntegration\Model\GetOrdersToSendPos $ordersToSendPos
     * @param StoreManagerInterface $storeManagerInterface
     * @param JsonFactory $jsonFactory
     * @param \Amore\PointsIntegration\Model\PosOrderSender $posOrderSender
     * @param Logger $pointsIntegrationLogger
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param DateTime $dateTime
     */
    public function __construct(
        Action\Context $context,
        \Amore\PointsIntegration\Model\GetOrdersToSendPos $ordersToSendPos,
        StoreManagerInterface $storeManagerInterface,
        JsonFactory $jsonFactory,
        \Amore\PointsIntegration\Model\PosOrderSender $posOrderSender,
        Logger $pointsIntegrationLogger,
        \Amore\PointsIntegration\Model\Source\Config $config,
        DateTime $dateTime
    ) {
        parent::__construct($context);
        $this->ordersToSendPos = $ordersToSendPos;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->jsonFactory = $jsonFactory;
        $this->posOrderSender = $posOrderSender;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->config = $config;
        $this->dateTime = $dateTime;
    }

    public function execute()
    {
        try {
            $websiteId = (int)$this->_request->getParam('websiteId');
            /** @var \Magento\Store\Model\Website $website */
            $website = $this->storeManagerInterface->getWebsite($websiteId);
            $stores = $website->getStores();

            $posIntegrationActive = $this->config->getActive($websiteId);

            if ($posIntegrationActive) {
                foreach ($stores as $store) {
                    $this->sendOrder($store->getId());
                }
            }

            $result = $this->jsonFactory->create();
            return $result->setData(['success' => true]);
        } catch (\Exception $exception) {
            $this->pointsIntegrationLogger->info("EXCEPTION OCCURRED WHILE SENDING ORDER TO POS ON ADMIN PANEL");
            $this->pointsIntegrationLogger->info($exception->getMessage());
            return $this->jsonFactory->create()->setData(['success' => false]);
        }
    }

    public function sendOrder($storeId)
    {
        $orders = $this->ordersToSendPos->getOrders($storeId);
        /** @var \Magento\Sales\Model\Order $order */
        foreach ($orders as $order) {
            if ($this->dateCheck($order)) {
                 $this->posOrderSender->posOrderSend($order);
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function dateCheck($order)
    {
        $result = false;
        if ($order->getStatus() == 'complete') {
            $updatedAt = $order->getUpdatedAt();
            $websiteId = $order->getStore()->getWebsiteId();
            $dateAfterToSendPos = is_null($this->config->getDaysForCompletedOrder($websiteId)) ? 7 : $this->config->getDaysForCompletedOrder($websiteId);

            $availableDateToSend = $this->dateTime->date('Y-m-d H:i:s', strtotime($updatedAt . '+' . $dateAfterToSendPos . 'days'));
            $currentDate = $this->dateTime->date();
            if ($currentDate > $availableDateToSend) {
                $result = true;
            }
        }
        return $result;
    }
}
