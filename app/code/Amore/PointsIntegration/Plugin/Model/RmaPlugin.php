<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-16
 * Time: 오전 10:48
 */
namespace Amore\PointsIntegration\Plugin\Model;

use Amore\PointsIntegration\Exception\PosPointsException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Setup\Exception;
use Magento\Store\Model\StoreManagerInterface;

class RmaPlugin
{
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Amore\PointsIntegration\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Amore\PointsIntegration\Model\PosReturnData
     */
    private $posReturnData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
     */
    private $request;

    /**
     * RmaPlugin constructor.
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param StoreManagerInterface $storeManager
     * @param \Amore\PointsIntegration\Logger\Logger $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param \Amore\PointsIntegration\Model\PosReturnData $posReturnData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     */
    public function __construct(
        \Amore\PointsIntegration\Model\Source\Config $config,
        StoreManagerInterface $storeManager,
        \Amore\PointsIntegration\Logger\Logger $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        \Amore\PointsIntegration\Model\PosReturnData $posReturnData,
        \Amore\PointsIntegration\Model\Connection\Request $request
    ) {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->posReturnData = $posReturnData;
        $this->request = $request;
    }

    public function beforeSaveRma(\Magento\Rma\Model\Rma $subject, $data)
    {
        $storeId = $subject->getStoreId();
        $websiteId = $this->getWebsiteByStore($storeId);
        $moduleEnableCheck = $this->config->getActive($websiteId);
        $rmaSendingEnableCheck = $this->config->getPosRmaActive($websiteId);

        $order = $subject->getOrder();
        $orderSendToPos = $order->getData('pos_order_send_check');
        $availableStatus = 'processed_closed';

        if ($moduleEnableCheck && $rmaSendingEnableCheck) {
            if ($orderSendToPos && $subject->getStatus() == $availableStatus) {
                try {
                    $this->returnOrderSend($subject, $websiteId);
                } catch (PosPointsException $e) {
                    throw new PosPointsException(__('POS Return Error : ' . $e->getMessage()));
                } catch (\Exception $e) {
                    throw new PosPointsException(__('POS Return Error : ' . $e->getMessage()));
                }
            }
        }
    }

    public function getWebsiteByStore($storeId)
    {
        $websiteId = 0;
        try {
            $websiteId = (int)$this->storeManager->getStore($storeId)->getWebsiteId();
        } catch (\Exception $exception) {
            $this->logger->info("============ Get Website ID Error ============");
            $this->logger->info($exception);
        }
        return $websiteId;
    }

    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            "eguana_bizconnect_operation_processed",
            [
                'topic_name' => 'amore.pos.points-integration.rma.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->json->serialize($sendData),
                'status' => $status,
                'result_message' => $this->json->serialize($responseData)
            ]
        );
    }

    public function responseCheck($response)
    {
        if (isset($response['data']['statusCode']) && $response['data']['statusCode'] == '200') {
            return 1;
        } else {
            return 0;
        }
    }

    /**
     * @param \Magento\Rma\Model\Rma $subject
     * @param int $websiteId
     */
    public function returnOrderSend(\Magento\Rma\Model\Rma $subject, int $websiteId): void
    {
        $rmaData = $this->posReturnData->getRmaData($subject);

        $response = $this->request->sendRequest($rmaData, $websiteId, 'customerOrder');

        $status = $this->responseCheck($response);

        $this->logging($rmaData, $response, $status);

        if ($status) {
            $this->posReturnData->updatePosSendCheck($subject->getId());
        } else {
            throw new PosPointsException(__("Pos Return Error. Please Check Log."));
        }
    }
}
