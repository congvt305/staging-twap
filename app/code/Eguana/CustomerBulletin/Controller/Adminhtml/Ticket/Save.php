<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Api\TicketRepositoryInterface;
use Eguana\CustomerBulletin\Controller\Adminhtml\AbstractController;
use Eguana\CustomerBulletin\Model\TicketFactory;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Backend\Model\View\Result\ForwardFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Message\Manager;
use Magento\Framework\Api\DataObjectHelper;

/**
 * Class Save to save ticket
 */
class Save extends AbstractController implements HttpPostActionInterface
{
    /**
     * @var Manager
     */
    protected $messageManager;

    /**
     * @var TicketRepositoryInterface
     */
    protected $ticketRepository;

    /**
     * @var TicketInterfaceFactory
     */
    protected $ticketFactory;

    /**
     * @var DataObjectHelper
     */
    protected $dataObjectHelper;

    /**
     * Save constructor.
     * @param Registry $registry
     * @param TicketFactory $ticketFactory
     * @param TicketRepositoryInterface $ticketRepository
     * @param PageFactory $resultPageFactory
     * @param ForwardFactory $resultForwardFactory
     * @param Manager $messageManager
     * @param DataObjectHelper $dataObjectHelper
     * @param Context $context
     */
    public function __construct(
        Registry $registry,
        TicketFactory $ticketFactory,
        TicketRepositoryInterface $ticketRepository,
        PageFactory $resultPageFactory,
        ForwardFactory $resultForwardFactory,
        Manager $messageManager,
        DataObjectHelper $dataObjectHelper,
        Context $context
    ) {
        $this->messageManager   = $messageManager;
        $this->ticketFactory     = $ticketFactory;
        $this->ticketRepository  = $ticketRepository;
        $this->dataObjectHelper  = $dataObjectHelper;
        parent::__construct(
            $resultPageFactory,
            $resultForwardFactory,
            $context,
            $registry,
            $ticketRepository
        );
    }

    /**
     * Save action
     *
     * @return \Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();

        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $id = $this->getRequest()->getParam('data_id');
            if ($id) {
                $model = $this->ticketRepository->getById($id);
            } else {
                unset($data['data_id']);
                $model = $this->ticketFactory->create();
            }

            try {
                $this->dataObjectHelper->populateWithArray($model, $data, TicketInterface::class);
                $this->ticketRepository->save($model);
                $this->messageManager->addSuccessMessage(__('You saved this data.'));
                $this->_getSession()->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath('*/*/edit', ['ticket_id' => $model->getId(), '_current' => true]);
                }
                return $resultRedirect->setPath('*/*/');
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (\Exception $e) {
                $this->messageManager->addException($e, __('Something went wrong while saving the data.'));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath('*/*/edit', ['ticket_id' => $this->getRequest()->getParam('ticket_id')]);
        }
        return $resultRedirect->setPath('*/*/');
    }
}
