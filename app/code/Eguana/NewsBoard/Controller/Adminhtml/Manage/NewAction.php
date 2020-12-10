<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 5:15 PM
 */
namespace Eguana\NewsBoard\Controller\Adminhtml\Manage;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\HttpGetActionInterface;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;

/**
 * This redirects to the edit controller
 * Class NewAction
 */
class NewAction extends Action implements HttpGetActionInterface
{
    /**
     * Execute Method
     * This method is used to redirect to Edit Controller
     *
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
