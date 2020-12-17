<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-17
 * Time: 오전 11:52
 */
namespace Amore\PointsIntegration\Plugin\Model;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;

class OrderRepositoryPlugin
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
     * InvoiceRepositoryPlugin constructor.
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

    public function afterSave(\Magento\Sales\Model\OrderRepository $subject, $result)
    {
        /** @var \Magento\Sales\Model\Order $order */
        $order = $result;

        $websiteId = $order->getStore()->getWebsiteId();
        $active = $this->config->getActive($websiteId);
        $posSendCheck = $order->getData('pos_order_send_check');
        $posOrderActive = $this->config->getPosOrderActive($websiteId);

        $orderData = '';
        $status = 0;

        $this->logger->info("========= ORDER SAVE AFTER PLUGIN ============");

        if ($active && $posOrderActive) {
            if (!$posSendCheck) {
                $this->logger->info("========= POS CHECK IS 0 ============");
                try {
                    $websiteId = $order->getStore()->getWebsiteId();
                    $orderData = $this->json->serialize($this->posOrderData->getOrderData($order));
                    $response = $this->request->sendRequest($orderData, $websiteId, 'customerOrder');

                    $status = $this->responseCheck($response);

                    if ($status) {
                        $this->posOrderData->updatePosSendCheck($order->getEntityId());
                    }
                } catch (NoSuchEntityException $exception) {
                    $this->logger->info("========= POS ORDER NO EXCEPTION ============");
                    $response = $exception->getMessage();
                } catch (\Exception $exception) {
                    $this->logger->info("========= POS ORDER EXCEPTION ============");
                    $response = $exception->getMessage();
                }

                $this->logging($orderData, $response, $status);
            }
        } else {
            $this->logger->info('========== POS ORDER REQUEST IS NOT COMPLETED DUE TO POINTS INTEGRATION MODULE INACTIVE ==========');
        }

        return $result;
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
