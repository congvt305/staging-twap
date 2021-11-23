<?php

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Connection\Request;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Model\RmaRepository;
use CJ\CouponCustomer\Model\PosCustomerGradeUpdater;

class PosReturnSender
{
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
     * @var PosReturnData
     */
    private $posReturnData;

    /**
     * @var RmaRepository
     */
    private $rmaRepository;

    /**
     * @var PosCustomerGradeUpdater
     */
    private $posCustomerGradeUpdater;

    /**
     * @param Request $request
     * @param ManagerInterface $eventManager
     * @param Json $json
     * @param Logger $pointsIntegrationLogger
     * @param PosReturnData $posReturnData
     * @param RmaRepository $rmaRepository
     * @param PosCustomerGradeUpdater $posCustomerGradeUpdater
     */
    public function __construct(
        Request $request,
        ManagerInterface $eventManager,
        Json $json,
        Logger $pointsIntegrationLogger,
        PosReturnData $posReturnData,
        RmaRepository $rmaRepository,
        PosCustomerGradeUpdater $posCustomerGradeUpdater
    ) {
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->posReturnData = $posReturnData;
        $this->rmaRepository = $rmaRepository;
        $this->posCustomerGradeUpdater = $posCustomerGradeUpdater;
    }

    /**
     * Send order to POS and update flag
     *
     * @param RmaInterface $rma
     */
    public function send(RmaInterface $rma)
    {
        $rmaData = null;
        $success = null;
        $websiteId = $rma->getOrder()->getStoreId();

        try {
            $rmaData = $this->posReturnData->getRmaData($rma);
            $response = $this->request->sendRequest($rmaData, $websiteId, 'customerOrder');
            $success = $this->request->sendRequest($response, $websiteId);
            if ($success) {
                $this->posReturnData->updatePosReturnOrderSendFlag($rma);
                // update Pos customer grade
                if ($rma->getCustomerId() !== null) {
                    $this->posCustomerGradeUpdater->updatePOSCustomerGrade($rma->getCustomerId(), $websiteId);
                }
            }
        } catch (\Exception $exception) {
            $message = 'POS Integration Fail: ' . $rma->getOrderIncrementId();
            $this->pointsIntegrationLogger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        } catch (\Throwable $exception) {
            $message = 'POS Integration Fail: ' . $rma->getOrderIncrementId();
            $this->pointsIntegrationLogger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        }

        $this->logging($rmaData, $response, $success);
    }

    /**
     * @param $response
     * @return bool
     */
    public function isSuccessResponse($response): bool
    {
        return isset($response['message']) && strtolower($response['message']) == 'success';
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
}
