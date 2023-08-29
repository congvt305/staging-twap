<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-29
 * Time: 오전 9:44
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Sales\Model\Order;
use CJ\CouponCustomer\Model\PosCustomerGradeUpdater;
use CJ\Middleware\Model\PosRequest as MiddlewareRequest;
use Psr\Log\LoggerInterface;
use Magento\Framework\Event\ManagerInterface;

class PosOrderSender extends MiddlewareRequest
{
    /**
     * @var PosOrderData
     */
    private $posOrderData;

    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;

    /**
     * @var PosCustomerGradeUpdater
     */
    private $posCustomerGradeUpdater;

    /**
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param LoggerInterface $logger
     * @param Config $config
     * @param PosOrderData $posOrderData
     * @param ManagerInterface $eventManager
     * @param PosCustomerGradeUpdater $posCustomerGradeUpdater
     */
    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        LoggerInterface $logger,
        Config $config,
        PosOrderData $posOrderData,
        ManagerInterface $eventManager,
        PosCustomerGradeUpdater $posCustomerGradeUpdater,
    ) {
        $this->posOrderData = $posOrderData;
        $this->eventManager = $eventManager;
        $this->posCustomerGradeUpdater = $posCustomerGradeUpdater;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
    }

    /**
     * Send order to POS and update flag
     *
     * @param Order $order
     */
    public function send(Order $order)
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $orderData = [];
        $status = false;
        try {
            $orderData = $this->posOrderData->getOrderData($order);
            $response = $this->sendRequest($orderData, $websiteId, 'customerOrder');
            $responseHandled = $this->handleResponse($response, 'customerOrder');
            $status = isset($responseHandled, $responseHandled['status']) ? $responseHandled['status'] : false;
            if ($status) {
                $this->posOrderData->updatePosPaidOrderSendFlag($order);
                // update Pos customer grade
                if ($order->getCustomerId() !== null) {
                    $this->posCustomerGradeUpdater->updatePOSCustomerGrade($order->getCustomerId(), $websiteId);
                }
            }
        } catch (\Exception $exception) {
            $message = 'POS Integration Fail: ' . $order->getIncrementId();
            $this->logger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        } catch (\Throwable $exception) {
            $message = 'POS Integration Fail: ' . $order->getIncrementId();
            $this->logger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        }

        $this->logging($orderData, $response, $status);
    }

    /**
     * @param $sendData
     * @param $responseData
     * @param $status
     * @return void
     */
    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            \Amore\CustomerRegistration\Model\POSSystem::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'amore.pos.points-integration.order.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->middlewareHelper->serializeData($sendData),
                'status' => $status,
                'result_message' => $this->middlewareHelper->serializeData($responseData)
            ]
        );
    }
}
