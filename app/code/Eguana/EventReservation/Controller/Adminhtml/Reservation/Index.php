<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 15/10/20
 * Time: 09:54 PM
 */
namespace Eguana\EventReservation\Controller\Adminhtml\Reservation;

use Eguana\EventReservation\Controller\Adminhtml\AbstractController;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\PageFactory;

/**
 * Show the grid view of event
 *
 * Class Index
 */
class Index extends AbstractController implements HttpGetActionInterface
{
    /**
     * Create events index page
     *
     * @return ResponseInterface|ResultInterface|PageFactory
     */
    public function execute()
    {
        /** @var PageFactory $resultPage */
        $resultPage = $this->resultPageFactory->create();
        $this->_init($resultPage)->getConfig()->getTitle()->prepend(__('Manage Events'));
        return $resultPage;
    }
}
