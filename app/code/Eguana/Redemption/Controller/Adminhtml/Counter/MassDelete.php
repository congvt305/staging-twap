<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 8/2/21
 * Time: 1:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Counter;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Used for mass delete action for deleting multiple participants
 *
 * Class MassDelete
 */
class MassDelete extends Action implements HttpPostActionInterface
{
    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Filter $filter
     * @param Context $context
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param RedemptionRepositoryInterface $redemptionRepository
     */
    public function __construct(
        Filter $filter,
        Context $context,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        RedemptionRepositoryInterface $redemptionRepository
    ) {
        $this->logger = $logger;
        $this->filter = $filter;
        $this->dataPersistor = $dataPersistor;
        $this->collectionFactory = $collectionFactory;
        $this->redemptionRepository = $redemptionRepository;
        parent::__construct($context);
    }

    /**
     * Execute action method to delete participants
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        try {
            $redemptionId = $this->dataPersistor->get('current_redemption_id');
            $collectionFilter = $this->filter->getCollection($this->collectionFactory->create());
            $collectionFilter->addFieldToFilter(
                "main_table.redemption_id",
                ["eq" => $redemptionId]
            );
            $collectionSize = $collectionFilter->getSize();
            $counterInc = [];
            foreach ($collectionFilter as $user) {
                if ($user->getStatus() == 1) {
                    if (isset($counterInc[$user->getCounterId()])) {
                        $counterInc[$user->getCounterId()] += 1;
                    } else {
                        $counterInc[$user->getCounterId()] = 1;
                    }
                }
                $user->delete();
            }
            if ($counterInc) {
                $this->updateCounterQunatities($redemptionId, $counterInc);
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 participant(s) have been deleted.', $collectionSize)
            );
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }

    /**
     * Update counters qunatities
     *
     * @param $redemptionId
     * @param $counterInc
     */
    private function updateCounterQunatities($redemptionId, $counterInc)
    {
        try {
            $redemptionDetail = $this->redemptionRepository->getById($redemptionId);
            foreach ($counterInc as $key => $value) {
                $counterKey = array_search($key, $redemptionDetail->getOfflineStoreId());
                $counterQty = $redemptionDetail->getCounterSeats();
                if ($counterKey !== false) {
                    $qty = $counterQty[$counterKey];
                    $qty += $value;
                    $counterQty[$counterKey] = $qty;
                    $redemptionDetail->setData('counter_seats', $counterQty);
                }
            }
            $this->redemptionRepository->save($redemptionDetail);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
