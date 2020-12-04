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
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * Class to add the Ticket
 */
class Add extends AbstractController implements HttpGetActionInterface
{
    /**
     * Forward to edit page
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        return $this->_forward('edit');
    }
}
