<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 08:12 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Eguana\EventReservation\Api\EventRepositoryInterface;
use Eguana\EventReservation\Controller\Adminhtml\AbstractController;
use Eguana\EventReservation\Model\Event;
use Eguana\EventReservation\Model\EventFactory;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\UrlRewrite\Model\UrlPersistInterface;
use Magento\UrlRewrite\Service\V1\Data\UrlRewrite;

/**
 * Delete events action class
 *
 * Class Delete
 */
class Delete extends AbstractController
{
    /**
     * @var EventFactory
     */
    private $eventFactory;

    /**
     * @var EventRepositoryInterface
     */
    private $eventRepository;

    /**
     * @var UrlPersistInterface
     */
    private $urlPersist;

    /**
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param EventFactory $eventFactory
     * @param UrlPersistInterface $urlPersist
     * @param EventRepositoryInterface $eventRepository
     */
    public function __construct(
        Context $context,
        PageFactory $resultPageFactory,
        EventFactory $eventFactory,
        UrlPersistInterface $urlPersist,
        EventRepositoryInterface $eventRepository
    ) {
        $this->urlPersist       = $urlPersist;
        $this->eventFactory     = $eventFactory;
        $this->eventRepository  = $eventRepository;
        parent::__construct($context, $resultPageFactory);
    }

    /**
     * Delete event action method
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        $resultRedirect = $this->resultRedirectFactory->create();
        $id = (int)$this->getRequest()->getParam('event_id');
        $model = $this->eventFactory->create();

        if ($id) {
            try {
                /** @var Event $model */
                $model = $this->eventRepository->getById($id);
                $model->delete();

                $this->urlPersist->deleteByData([
                    UrlRewrite::ENTITY_ID => $model->getId(),
                    UrlRewrite::ENTITY_TYPE => 'custom',
                    UrlRewrite::REDIRECT_TYPE => 0,
                    UrlRewrite::TARGET_PATH => 'event/reservation/index/id/' . $model->getId()
                ]);

                $this->messageManager->addSuccessMessage(__('Event was successfully deleted'));
                return $resultRedirect->setPath('*/*/index');
            } catch (\Exception $exception) {
                $this->messageManager->addErrorMessage($exception->getMessage());
                return $resultRedirect->setPath('*/*/index');
            }
        }

        $this->messageManager->addErrorMessage(__('Event could not be deleted'));
        return $resultRedirect->setPath('*/*/index');
    }
}
