<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 2/11/20
 * Time: 7:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Counter;

use Eguana\Redemption\Model\Counter;
use Eguana\Redemption\Model\ResourceModel\Counter\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * use that class for Mass Redemption action
 *
 * Class MassRedemption
 */
class MassRedemption extends Action implements HttpPostActionInterface
{
    /**
     * Authorization level of a basic admin session
     *
     * @see _isAllowed()
     */
    const ADMIN_RESOURCE = 'Eguana_Redemption::redemption';

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var DateTime
     */
    private $date;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * MassRedemption constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param DateTime $date
     * @param LoggerInterface $logger
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        DateTime $date,
        LoggerInterface $logger,
        DataPersistorInterface $dataPersistor
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
        $this->date = $date;
        $this->dataPersistor = $dataPersistor;
        parent::__construct($context);
    }

    /**
     * Execute action
     *
     * @return Redirect|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $redemptionId = $this->dataPersistor->get('current_redemption_id');
            $date = $this->date->gmtDate();
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection->addFieldToFilter(
                "main_table.redemption_id",
                ["eq" => $redemptionId]
            );
            foreach ($collection as $item) {
                $item->setStatus(Counter::STATUS_REDEMPTION);
                $item->setRedeemDate($date);
                $item->save();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been redeemed.', $collection->getSize())
            );
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
