<?php

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use CJ\Middleware\Helper\Data as MiddlewareHelper;
use CJ\Middleware\Model\PosRequest;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\HTTP\Client\Curl;
use Magento\Rma\Api\Data\RmaInterface;
use Magento\Rma\Model\RmaRepository;
use CJ\CouponCustomer\Model\PosCustomerGradeUpdater;
use Psr\Log\LoggerInterface;

class PosReturnSender extends PosRequest
{
    /**
     * @var ManagerInterface
     */
    private $eventManager;

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
     * @param Curl $curl
     * @param MiddlewareHelper $middlewareHelper
     * @param LoggerInterface $logger
     * @param Config $config
     * @param ManagerInterface $eventManager
     * @param PosReturnData $posReturnData
     * @param RmaRepository $rmaRepository
     * @param PosCustomerGradeUpdater $posCustomerGradeUpdater
     */

    public function __construct(
        Curl $curl,
        MiddlewareHelper $middlewareHelper,
        LoggerInterface $logger,
        Config $config,
        ManagerInterface $eventManager,
        PosReturnData $posReturnData,
        RmaRepository $rmaRepository,
        PosCustomerGradeUpdater $posCustomerGradeUpdater
    ) {
        $this->eventManager = $eventManager;
        $this->posReturnData = $posReturnData;
        $this->rmaRepository = $rmaRepository;
        $this->posCustomerGradeUpdater = $posCustomerGradeUpdater;
        parent::__construct($curl, $middlewareHelper, $logger, $config);
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
            $response = $this->sendRequest($rmaData, $websiteId, 'customerOrder');
            $responseHandled = $this->handleResponse($response, 'customerOrder');
            $success = isset($responseHandled, $responseHandled['status']) ? $responseHandled['status'] : false;
            if ($success) {
                $this->posReturnData->updatePosReturnOrderSendFlag($rma);
                // update Pos customer grade
                if ($rma->getCustomerId() !== null) {
                    $this->posCustomerGradeUpdater->updatePOSCustomerGrade($rma->getCustomerId(), $websiteId);
                }
            }
        } catch (\Exception $exception) {
            $message = 'POS Integration Fail: ' . $rma->getOrderIncrementId();
            $this->logger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        } catch (\Throwable $exception) {
            $message = 'POS Integration Fail: ' . $rma->getOrderIncrementId();
            $this->logger->info($message . $exception->getMessage());
            $response = $exception->getMessage();
        }

        $this->logging($rmaData, $response, $success);
    }

    public function logging($sendData, $responseData, $status)
    {
        $this->eventManager->dispatch(
            \Amore\CustomerRegistration\Model\POSSystem::EGUANA_BIZCONNECT_OPERATION_PROCESSED,
            [
                'topic_name' => 'amore.pos.points-integration.rma.auto',
                'direction' => 'outgoing',
                'to' => "POS",
                'serialized_data' => $this->middlewareHelper->serializeData($sendData),
                'status' => $status,
                'result_message' => $this->middlewareHelper->serializeData($responseData)
            ]
        );
    }
}
