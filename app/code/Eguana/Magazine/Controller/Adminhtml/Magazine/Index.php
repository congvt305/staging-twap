<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 7:14 AM
 */

namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Magento\Backend\App\Action;

/**
  * Action for index
  * class index
  */
class Index extends Action
{
    /**
     * Execute the index action
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('grid');
    }
}
