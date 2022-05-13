<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-29
 * Time: 오전 9:44
 */

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Logger\Logger;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\Order;
use CJ\CouponCustomer\Model\PosCustomerGradeUpdater;

class PosOrderSender
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
     * @var \Magento\Framework\Event\ManagerInterface
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
     * @var PosCustomerGradeUpdater
     */
    private $posCustomerGradeUpdater;
    /**
     * PosOrderSender constructor.
     * @param PosOrderData $posOrderData
     * @param Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param Source\Config $PointsIntegrationConfig
     * @param Logger $pointsIntegrationLogger
     */
    public function __construct(
        \Amore\PointsIntegration\Model\PosOrderData       $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface         $eventManager,
        Json                                              $json,
        \Amore\PointsIntegration\Model\Source\Config      $PointsIntegrationConfig,
        Logger                                            $pointsIntegrationLogger,
        PosCustomerGradeUpdater                           $posCustomerGradeUpdater
    ) {
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->posCustomerGradeUpdater = $posCustomerGradeUpdater;
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
            $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');
            $status = $this->request->responseCheck($response, $websiteId);
            if ($status) {
                $this->posOrderData->updatePosPaidOrderSendFlag($order);
                // update Pos customer grade
                if ($order->getCustomerId() !== null) {
                    $this->posCustomerGradeUpdater->updatePOSCustomerGrade($order->getCustomerId(), $websiteId);
                }
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
