<?php
/**
 * @author Eguana Commerce
 * @copyright Copyright (c) 2020 Eguana {https://eguanacommerce.com}
 *  Created by PhpStorm
 *  User: Sonia Park
 *  Date: 3/17/20, 6:50 AM
 *
 */

namespace Eguana\BizConnect\Observer;

use Eguana\BizConnect\Model\OperationLogRepository;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Store\Api\StoreRepositoryInterface;

class OperationLogSave implements ObserverInterface
{
    /**
     * @var OperationLogRepository
     */
    private $operationLogRepository;
    /**
     * @var StoreRepositoryInterface
     */
    private $storeRepository;

    public function __construct(
        OperationLogRepository $operationLogRepository,
        StoreRepositoryInterface $storeRepository
    ) {

        $this->operationLogRepository = $operationLogRepository;
        $this->storeRepository = $storeRepository;
    }

    /**
     * Log message after it was processed.
     *
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        $this->logOperation($observer);
    }

    private function getStoreId($to)
    {
        $storeCode = '';
        if (strtolower($to) == 'spes') {
            $storeCode = 'kr';
        } elseif (strtolower($to) == 'tempostar') {
            $storeCode = 'jp';
        } elseif (strtolower($to) == 'tw_lageige_website') {
            $storeCode = 'tw_laneige';
        } elseif (strtolower($to) == 'base') {
            $storeCode = 'default';
        }
        $storeId = null;
        if ($storeCode) {
            $storeId = $this->storeRepository->get($storeCode)->getId();
        }
        return $storeId;
    }


    /**
     * @param $observer
     */
    private function logOperation($observer)
    {
        $topicName = $observer->getEvent()->getTopicName();
        $serializedData = $observer->getEvent()->getSerializedData();
        $to = $observer->getEvent()->getTo();
        $direction = $observer->getEvent()->getDirection();
        $status = $observer->getEvent()->getStatus();
        $resultMessage = $observer->getEvent()->getResultMessage();

        $storeId = $this->getStoreId($to);
        $loggedOperatiolnId = $this->operationLogRepository->createOrUpdateMessage(
            $topicName,
            $serializedData,
            $to,
            $status,
            $direction,
            $this->getStoreId($to)
        );

        $this->operationLogRepository->addLogToOperation($loggedOperatiolnId, $resultMessage);
    }


}
