<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 11/10/20
 * Time: 06:48 PM
 */
namespace Eguana\CustomerBulletin\Controller\Adminhtml\Ticket;

use Eguana\CustomerBulletin\Controller\Adminhtml\AbstractController;
use Magento\Backend\Model\View\Result\Page;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Show the grid view of ticket
 */
class Index extends AbstractController implements HttpGetActionInterface
{
    /**
     * Create index page
     *
     * @return ResponseInterface|ResultInterface|Page
     */

    public function execute()
    {
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend((__('Manage Tickets')));
        return $resultPage;
    }
}
