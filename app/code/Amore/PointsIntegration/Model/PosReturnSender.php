<?php

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Connection\Request;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Model\RmaRepository;
use CJ\CouponCustomer\Helper\UpdatePOSCustomerGradeHelper;

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
     * @var UpdatePOSCustomerGradeHelper
     */
    private $updatePOSCustomerGradeHelper;

    /**
     * @param Request $request
     * @param ManagerInterface $eventManager
     * @param Json $json
     * @param Logger $pointsIntegrationLogger
     * @param PosReturnData $posReturnData
     * @param RmaRepository $rmaRepository
     * @param UpdatePOSCustomerGradeHelper $updatePOSCustomerGradeHelper
     */
    public function __construct(
        Request $request,
        ManagerInterface $eventManager,
        Json $json,
        Logger $pointsIntegrationLogger,
        PosReturnData $posReturnData,
        RmaRepository $rmaRepository,
        UpdatePOSCustomerGradeHelper $updatePOSCustomerGradeHelper
    ) {
        $this->request = $request;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->pointsIntegrationLogger = $pointsIntegrationLogger;
        $this->posReturnData = $posReturnData;
        $this->rmaRepository = $rmaRepository;
        $this->updatePOSCustomerGradeHelper = $updatePOSCustomerGradeHelper;
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
            $success = $this->isSuccessResponse($response);
            if ($success) {
                $this->posReturnData->updatePosReturnOrderSendFlag($rma);
                if($rma->getCustomerId() !== null){
                    $this->updatePOSCustomerGradeHelper->updatePOSCustomerGrade($rma->getCustomerId());
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
