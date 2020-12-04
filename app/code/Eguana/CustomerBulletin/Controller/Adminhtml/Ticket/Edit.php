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

use Eguana\CustomerBulletin\Controller\Adminhtml\AbstractController;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class to edit/update the ticket
 */
class Edit extends AbstractController implements HttpGetActionInterface
{
    /**
     * Edit CMS block
     *
     * @return ResultInterface
     */
    public function execute() : ResultInterface
    {
        /** @var Page $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $resultPage->setActiveMenu('Eguana_CustomerBulletin::ticket_manage');

        // 1. Get ID and create model
        $ticketId = $this->getRequest()->getParam('ticket_id');

        // 2. Initial checking
        if ($ticketId) {
            $resultPage->addBreadcrumb(__('Edit Ticket'), __('Edit Ticket'));
            $resultPage->getConfig()->getTitle()->prepend(
                $this->ticketRepository->getById($ticketId)->getTitle()
            );
        } else {
            $resultPage->addBreadcrumb(__('New Ticket'), __('New Ticket'));
            $resultPage->getConfig()->getTitle()->prepend(__('New Ticket'));
        }
        return $resultPage;
    }
}
