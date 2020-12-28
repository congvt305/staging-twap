<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 1:27 PM
 */

namespace Eguana\ChangeStatus\Cron;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Api\OrderRepositoryInterface;

class OrderProcessingToComplete
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Eguana\ChangeStatus\Model\Source\Config
     */
    private $config;
    /**
     * @var \Eguana\ChangeStatus\Model\GetCompletedOrders
     */
    private $completedOrders;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
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
    private $PointsIntegrationConfig;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * OrderProcessingToComplete constructor.
     * @param \Magento\Store\Model\StoreManagerInterface $storeManagerInterface
     * @param \Eguana\ChangeStatus\Model\Source\Config $config
     * @param \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders
     * @param OrderRepositoryInterface $orderRepository
     * @param \Amore\PointsIntegration\Model\PosOrderData $posOrderData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param Config $PointsIntegrationConfig
     * @param Logger $logger
     */
    public function __construct(
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\ChangeStatus\Model\Source\Config $config,
        \Eguana\ChangeStatus\Model\GetCompletedOrders $completedOrders,
        OrderRepositoryInterface $orderRepository,
        \Amore\PointsIntegration\Model\PosOrderData $posOrderData,
        \Amore\PointsIntegration\Model\Connection\Request $request,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        Config $PointsIntegrationConfig,
        Logger $logger
    ) {
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->completedOrders = $completedOrders;
        $this->orderRepository = $orderRepository;
        $this->posOrderData = $posOrderData;
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->PointsIntegrationConfig = $PointsIntegrationConfig;
        $this->logger = $logger;
    }

    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $isActive = $this->config->getChangeOrderStatusActive($store->getId());

            if ($isActive) {
                $orderList = $this->completedOrders->getCompletedOrder($store->getId());

                foreach ($orderList as $order) {
                    $order->setStatus('complete');
                    $order->setState('complete');
                    $this->orderRepository->save($order);
                }
            }
        }
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function posOrderSend($order)
    {
        $websiteId = $order->getStore()->getWebsiteId();
        $active = $this->PointsIntegrationConfig->getActive($websiteId);
        $orderSendActive = $this->PointsIntegrationConfig->getPosOrderActive($websiteId);

        $orderData = '';
        $status = 0;

        if ($active && $orderSendActive) {
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
