<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 16/10/20
 * Time: 12:30 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\UserReservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Helper\ConfigData;
use Eguana\EventReservation\Model\Email\EmailSender;
use Eguana\EventReservation\Model\ResourceModel\UserReservation\CollectionFactory;
use Eguana\EventReservation\Model\UserReservation;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Change status to pending of multiple reservations
 *
 * Class MassPending
 */
class MassPending extends Action implements HttpPostActionInterface
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
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Filter $filter
     * @param ConfigData $configHelper
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        Filter $filter,
        ConfigData $configHelper,
        EmailSender $emailSender,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository
    ) {
        $this->logger               = $logger;
        $this->filter               = $filter;
        $this->emailSender          = $emailSender;
        $this->configHelper         = $configHelper;
        $this->dataPersistor        = $dataPersistor;
        $this->eventRepository      = $eventRepository;
        $this->collectionFactory    = $collectionFactory;
        parent::__construct($context);
    }

    /**
     * Execute action to enable events
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        try {
            $eventId = $this->dataPersistor->get('current_event_id');
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);

            try {
                $event = $this->eventRepository->getById($eventId);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $this->messageManager->addErrorMessage(__('No record exists against this request.'));
                return $resultRedirect->setPath($this->_redirect->getRefererUrl());
            }

            $storeId = $event->getStoreId() ? $event->getStoreId() : 0;
            $storeId = is_array($storeId) ? $storeId[0] : $storeId;

            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $collection->addFieldToFilter(
                "main_table.event_id",
                ["eq" => $eventId]
            );

            foreach ($collection as $item) {
                $item->setStatus(UserReservation::STATUS_PENDING);
                $item->save();

                if ($this->configHelper->getCustomerEmailEnabledForAdmin($storeId) == 1) {
                    $this->emailSender->sendEmailToCustomer(
                        $item->getId(),
                        'pending'
                    );
                }
                if ($this->configHelper->getStaffEmailEnabledForAdmin($storeId) == 1) {
                    $this->emailSender->sendEmailToStaff(
                        $item->getId(),
                        'pending'
                    );
                }
            }

            $this->messageManager->addSuccessMessage(
                __('A total of %1 user reservation(s) have been pending.', $collection->getSize())
            );
        } catch (LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
        }

        return $resultRedirect->setPath($this->_redirect->getRefererUrl());
    }
}
