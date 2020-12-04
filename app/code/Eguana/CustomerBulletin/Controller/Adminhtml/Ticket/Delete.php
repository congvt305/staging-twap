<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Controller\Adminhtml\AbstractController;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\RedirectFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * Class to delete the ticket
 */
class Delete extends AbstractController
{
    /**
     * @return ResponseInterface|RedirectFactory|ResultInterface
     */
    public function execute()
    {
        /** @var RedirectFactory $resultRedirect */
        $resultRedirect = $this->resultRedirectFactory->create();
        $ticketId = $this->getRequest()->getParam('ticket_id');

        if ($ticketId) {
            try {
                $this->ticketRepository->deleteById($ticketId);
                $this->messageManager->addSuccessMessage(__('The ticket has been deleted.'));
                $resultRedirect->setPath('*/*/');
                return $resultRedirect;
            } catch (NoSuchEntityException $e) {
                $this->messageManager->addErrorMessage(__('The ticket no longer exists.'));
                return $resultRedirect->setPath('*/*/');
            } catch (LocalizedException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
                return $resultRedirect->setPath('*/*/', ['ticket_id' => $ticketId]);
            } catch (\Exception $e) {
                $this->messageManager->addErrorMessage(__('There was a problem while deleting the data'));
                return $resultRedirect->setPath('*/*/edit', ['ticket_id' => $ticketId]);
            }
        }

        $this->messageManager->addErrorMessage(__('We can\'t find the ticket to delete.'));
        $resultRedirect->setPath('*/*/');

        return $resultRedirect;
    }
}
