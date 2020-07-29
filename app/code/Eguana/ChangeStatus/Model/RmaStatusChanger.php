<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:23 AM
 */

namespace Eguana\ChangeStatus\Model;

use Eguana\ChangeStatus\Model\Source\Config;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class RmaStatusChanger
{
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;
    /**
     * @var DateTime
     */
    private $dateTime;
    /**
     * @var Source\Config
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Rma\Model\ResourceModel\Item\CollectionFactory
     */
    private $rmaItemFactory;
    /**
     * @var \Magento\Rma\Model\Rma\Source\StatusFactory
     */
    private $rmaStatusFactory;
    /**
     * @var \Magento\Rma\Model\Rma\Status\HistoryFactory
     */
    private $historyFactory;

    /**
     * GetPendingRma constructor.
     * @param RmaRepositoryInterface $rmaRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param DateTime $dateTime
     * @param Source\Config $config
     * @param StoreManagerInterface $storeManagerInterface
     * @param LoggerInterface $logger
     */
    public function __construct(
        RmaRepositoryInterface $rmaRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        DateTime $dateTime,
        Config $config,
        StoreManagerInterface $storeManagerInterface,
        LoggerInterface $logger,
        \Magento\Rma\Model\ResourceModel\Item\CollectionFactory $rmaItemFactory,
        \Magento\Rma\Model\Rma\Source\StatusFactory $rmaStatusFactory,
        \Magento\Rma\Model\Rma\Status\HistoryFactory $historyFactory
    ) {
        $this->rmaRepository = $rmaRepository;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->dateTime = $dateTime;
        $this->config = $config;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->logger = $logger;
        $this->rmaItemFactory = $rmaItemFactory;
        $this->rmaStatusFactory = $rmaStatusFactory;
        $this->historyFactory = $historyFactory;
    }

    public function changeRmaStatus()
    {
        $stores = $this->storeManagerInterface->getStores();
        try {
            foreach ($stores as $store) {
                $isCustomRmaActive = $this->config->getCustomRmaActive($store->getId());
                if ($isCustomRmaActive) {
                    $pendingRmaList = $this->getPendingRma($store->getId());
                    foreach ($pendingRmaList as $pendingRma) {
                        $this->changeRmaStatuses($pendingRma);
                    }
                }
            }
        } catch (\Exception $exception) {
            $this->logger->debug("EXCEPTION OCCURRED DURING CHANGING PENDING RMA TO AUTHORIZED.");
            $this->logger->debug($exception->getMessage());
        }
    }

    public function getPendingRma($storeId)
    {
        $dateTime = $this->dateTime->date();
        $periodForAuthorizationChanges = $this->config->getRmaAutoAuthorizationDays($storeId);

        $periodAppliedDate = $this->dateTime->date('Y-m-d H:i:s', strtotime('now -' . $periodForAuthorizationChanges . ' day'));

        $searchCriteria = $this->searchCriteriaBuilder
            ->addFilter('status', 'pending', 'eq')
            ->addFilter('date_requested', $periodAppliedDate, 'lteq')
            ->create();

        $pendingRmaList = $this->rmaRepository->getList($searchCriteria)->getItems();

        return $pendingRmaList;
    }

    /**
     * @param $rma \Magento\Rma\Model\Rma
     */
    public function changeRmaStatuses($rma)
    {
        try {
            /** @var $sourceStatus \Magento\Rma\Model\Rma\Source\Status */
            $sourceStatus = $this->rmaStatusFactory->create();
            $rma->setStatus($sourceStatus->getStatusByItems($this->getRmaItemStatus($rma->getEntityId())))->setIsUpdate(1);

            if (!$rma->saveRma($this->getRmaRequestData($rma))) {
                $this->logger->critical(__("Cron Could not save RMA %1.", $rma->getEntityId()));
            }

            /** @var $statusHistory \Magento\Rma\Model\Rma\Status\History */
            $statusHistory = $this->historyFactory->create();
            $statusHistory->setRmaEntityId($rma->getEntityId());
            if ($rma->getIsSendAuthEmail()) {
                $statusHistory->sendAuthorizeEmail();
            }
            if ($rma->getStatus() !== $rma->getOrigData('status')) {
                $statusHistory->saveSystemComment();
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->logger->critical('Change Status EXCEPTION : ' . $e->getMessage());
        } catch (\Exception $e) {
            $this->logger->critical('Change Status EXCEPTION : ' . $e->getMessage());
        }
    }

    /** @param \Magento\Rma\Model\Rma $rma */
    public function getRmaRequestData($rma)
    {
        $requestData = [
            'entity_id' => $rma->getEntityId(),
            'title' => '',
            'number' => '',
            'items' => $this->getRmaItemData($rma->getEntityId())
        ];
        return $requestData;
    }

    public function getRmaItemData($rmaId)
    {
        $itemData = [];
        $rmaItemCollection = $this->getRmaItemCollection($rmaId);
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItemCollection as $rmaItem) {
            $itemData[$rmaItem->getEntityId()] = [
                'qty_authorized' => $rmaItem->getQtyRequested(),
                'status' => 'authorized',
                'order_item_id' => $rmaItem->getOrderItemId(),
                'entity_id' => $rmaItem->getEntityId(),
                'resolution' => $rmaItem->getResolution()
            ];
        }
        return $itemData;
    }

    public function getRmaItemStatus($rmaId)
    {
        $statuses = [];
        $rmaItemCollection = $this->getRmaItemCollection($rmaId);
        /** @var \Magento\Rma\Model\Item $rmaItem */
        foreach ($rmaItemCollection as $rmaItem) {
            $statuses[$rmaItem->getId()] = 'authorized';
        }
        return $statuses;
    }

    public function getRmaItemCollection($rmaId)
    {
        /** @var \Magento\Rma\Model\ResourceModel\Item\Collection $rmaItemCollection */
        $rmaItemCollection = $this->rmaItemFactory->create();
        $rmaItemCollection->addFieldToFilter('rma_entity_id', $rmaId)
            ->addAttributeToSelect("*");

        return $rmaItemCollection;
    }
}
