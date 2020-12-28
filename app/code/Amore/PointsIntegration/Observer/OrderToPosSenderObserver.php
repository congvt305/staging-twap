<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 4:55
 */

namespace Amore\PointsIntegration\Observer;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class OrderToPosSenderObserver implements ObserverInterface
{
    /**
     * @var \Amore\PointsIntegration\Model\PosOrderData
     */
    private $posOrderData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
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
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderToPosSenderObserver constructor.
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        Config $config,
        Logger $logger
    ) {
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->config = $config;
        $this->logger = $logger;
    }

    public function execute(Observer $observer)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $observer->getEvent()->getOrder();

        $websiteId = $order->getStore()->getWebsiteId();
        $active = $this->config->getActive($websiteId);
        $orderSendActive = $this->config->getPosOrderActive($websiteId);

        $orderData = '';
        $status = 0;

        $posSendOrderStatusCheck = $this->orderStatusValidator($order);

        if ($active && $orderSendActive) {
            if ($posSendOrderStatusCheck) {
                try {
                    $orderData = $this->posOrderData->getOrderData($order);

                    $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');
                    $status = $this->responseCheck($response);

                    if ($status) {
                        $this->posOrderData->updatePosSendCheck($order->getEntityId());
                    }
                } catch (NoSuchEntityException $exception) {
                    $this->logger->info("===== OBSERVER NO SUCH ENTITY EXCEPTION =====");
                    $this->logger->info($exception->getMessage());
                    $response = $exception->getMessage();
                } catch (\Exception $exception) {
                    $this->logger->info("===== OBSERVER EXCEPTION =====");
                    $this->logger->info($exception->getMessage());
                    $response = $exception->getMessage();
                }

                $this->logging($orderData, $response, $status);
            }
        } else {
            $this->logger->info('POS ORDER REQUEST FOR ORDER : ' . $order->getIncrementId() . ' IS NOT COMPLETED DUE TO POINTS INTEGRATION MODULE INACTIVE');
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function orderStatusValidator(\Magento\Sales\Model\Order $order)
    {
        $posOrderRequestStatus = false;

        $validStatus = 'complete';
        $validState = 'complete';

        $validStateAndStatus = ($validState && $validStatus);
        $canShip = $order->canShip();
        $posSendCheck = $order->getData('pos_order_send_check');

        if ($validStateAndStatus && !$canShip && !$posSendCheck) {
            $posOrderRequestStatus = true;
        }
        return $posOrderRequestStatus;
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
