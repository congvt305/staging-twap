<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:17 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Magento\Backend\App\Action;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;

/**
 * Action for add new magazine button
 *
 * Class NewAction
 */

class NewAction extends Action
{
    /**
     * Execute the add new magazine action
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
