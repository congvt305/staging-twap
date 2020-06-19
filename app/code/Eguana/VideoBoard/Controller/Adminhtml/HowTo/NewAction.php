<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/6/20
 * Time: 7:13 PM
 */
namespace Eguana\VideoBoard\Controller\Adminhtml\HowTo;

use Magento\Framework\App\Action\Action;

/**

 * Class NewAction
 * Eguana\VideoBoard\Controller\Adminhtml\HowTo
 */
class NewAction extends Action
{

    /**
     * Execute Method
     * This method is used to redirect to Edit Controller
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
