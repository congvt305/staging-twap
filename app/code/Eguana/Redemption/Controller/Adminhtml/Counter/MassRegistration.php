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
use Magento\Ui\Component\MassAction\Filter;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

/**
 * use that class for Mass Registration action
 *
 * Class MassRegistration
 */
class MassRegistration extends Action implements HttpPostActionInterface
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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * MassRegistration constructor.
     *
     * @param Context $context
     * @param Filter $filter
     * @param CollectionFactory $collectionFactory
     * @param LoggerInterface $logger
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        Context $context,
        Filter $filter,
        CollectionFactory $collectionFactory,
        LoggerInterface $logger,
        DataPersistorInterface $dataPersistor
    ) {
        $this->filter = $filter;
        $this->collectionFactory = $collectionFactory;
        $this->logger = $logger;
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
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection->addFieldToFilter(
                "main_table.redemption_id",
                ["eq" => $redemptionId]
            );
            foreach ($collection as $item) {
                $item->setStatus(Counter::STATUS_REGISTRATION);
                $item->setRedeemDate(null);
                $item->save();
            }
            $this->messageManager->addSuccessMessage(
                __('A total of %1 record(s) have been registered.', $collection->getSize())
            );
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $resultRedirect->setUrl($this->_redirect->getRefererUrl());
    }
}
