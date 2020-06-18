<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/18/20
 * Time: 3:10 AM
 */

namespace Eguana\Magazine\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

/**
 * Action for index index
 *
 * Class Index
 */
class Index extends Action
{
    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }

}
