<?php

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Connection\Request;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;

class POSCancelledOrderSender
{
    /**
     * @var PosOrderData
     */
    private $posOrderData;
    /**
     * @var Connection\Request
     */
    private $request;
    /**
     * @var ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

    /**
     * PosOrderSender constructor.
     * @param PosOrderData $posOrderData
     * @param Connection\Request $request
     * @param ManagerInterface $eventManager
     * @param Json $json
     * @param Logger $pointsIntegrationLogger
     */
    public function __construct(
        PosOrderData     $posOrderData,
        Request          $request,
        ManagerInterface $eventManager,
        Json             $json,
        Logger           $pointsIntegrationLogger
    )
    {
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
    }

    /**
     * @param Order $order
     */
    public function send(Order $order)
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $orderData = [];
        $status = false;

        try {
            $orderData = $this->posOrderData->getCancelledOrderData($order);
            $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');
            $status = $this->responseCheck($response);
            if ($status) {
                $this->posOrderData->updatePosCancelledOrderSendFlag($order);
            }
        } catch (\Exception $exception) {
            $message = 'POS Integration Fail: ' . $order->getIncrementId();
            $this->pointsIntegrationLogger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        } catch (\Throwable $exception) {
            $message = 'POS Integration Fail: ' . $order->getIncrementId();
            $this->pointsIntegrationLogger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        }

        $this->logging($orderData, $response, $status);
    }

    /**
     * @param $response
     * @return bool
     */
    public function responseCheck($response): bool
    {
        return isset($response['message']) && strtolower($response['message']) == 'success';
    }

    /**
     * @param $sendData
     * @param $responseData
     * @param $status
     */
    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.pos.points-integration.order.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->json->serialize($sendData),
                'status' => $status,
                'result_message' => $this->json->serialize($responseData)
            ]
        );
    }
}
