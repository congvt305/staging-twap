<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 09:48 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Controller\Adminhtml\AbstractController;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Psr\Log\LoggerInterface;

/**
 * Action class to edit/update the event
 *
 * Class Edit
 */
class Edit extends AbstractController implements HttpGetActionInterface
{
    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param LoggerInterface $logger
     * @param DataPersistorInterface $dataPersistor
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        LoggerInterface $logger,
        DataPersistorInterface $dataPersistor,
        EventRepositoryInterface $eventRepository
    ) {
        $this->logger           = $logger;
        $this->dataPersistor    = $dataPersistor;
        $this->eventRepository  = $eventRepository;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Method to edit the event data
     *
     * @return Page|ResponseInterface|ResultInterface
     */
    public function execute()
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $eventId = $this->getRequest()->getParam('event_id');

        if ($eventId) {
            try {
                $event = $this->eventRepository->getById($eventId);
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
                $resultPage = $this->resultRedirectFactory->create();
                return $resultPage->setPath('*/*/');
            }

            $this->dataPersistor->set('current_event_id', $eventId);
            $this->_init($resultPage)->addBreadcrumb(__('Edit Event'), __('Edit Event'));
            $resultPage->getConfig()->getTitle()->prepend(
                $event->getTitle()
            );
        } else {
            $this->_init($resultPage)->addBreadcrumb(__('New Event'), __('New Event'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Event'));
        }
        return $resultPage;
    }
}
