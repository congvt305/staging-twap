<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Adminhtml\Redemption;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

/**
 * This redirects to the edit controller
 * Class NewAction
 */
class NewAction extends Action
{
    /**
     * Execute Method
     * This method is used to redirect to Edit Controller
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('edit');
    }
}
