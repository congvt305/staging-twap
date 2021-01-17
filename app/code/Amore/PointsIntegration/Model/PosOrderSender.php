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
     * @var Source\Config
     */
    private $PointsIntegrationConfig;
    /**
     * @var Logger
     */
    private $pointsIntegrationLogger;

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
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        \Amore\PointsIntegration\Model\Source\Config $PointsIntegrationConfig,
        Logger $pointsIntegrationLogger
    ) {
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function posOrderSend($order)
    {
        $websiteId = $order->getStore()->getWebsiteId();
//        $active = $this->PointsIntegrationConfig->getActive($websiteId);
        $orderSendActive = $this->PointsIntegrationConfig->getPosOrderActive($websiteId);

        $orderData = '';
        $status = 0;
        $posSendCheck = $order->getData('pos_order_send_check');

//        if ($active && $orderSendActive) {
        if ($orderSendActive) {
            if (!$posSendCheck) {
                try {
                    $orderData = $this->posOrderData->getOrderData($order);

                    $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');
                    $status = $this->responseCheck($response);

                    if ($status) {
                        $this->posOrderData->updatePosSendCheck($order->getEntityId());
                    }
                } catch (NoSuchEntityException $exception) {
                    $this->pointsIntegrationLogger->info("===== NO SUCH ENTITY EXCEPTION =====");
                    $this->pointsIntegrationLogger->info($exception->getMessage());
                    $response = $exception->getMessage();
                } catch (\Exception $exception) {
                    $this->pointsIntegrationLogger->info("===== EXCEPTION =====");
                    $this->pointsIntegrationLogger->info($exception->getMessage());
                    $response = $exception->getMessage();
                }

                $this->logging($orderData, $response, $status);
            }
        } else {
            $this->logger->info('POS ORDER REQUEST FOR ORDER : ' . $order->getIncrementId() . ' IS NOT COMPLETED DUE TO POINTS INTEGRATION MODULE INACTIVE');
        }
    }

    public function responseCheck($response)
    {
        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            return 1;
        } else {
            return 0;
        }
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
