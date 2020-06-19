<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 16/6/20
 * Time: 12:32 PM
 */
namespace Eguana\VideoBoard\Controller\Index;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Action\Action;

/**
 * This class is used to add load the layout and render data
 *
 * Class Index
 * Eguana\VideoBoard\Controller\Index
 */
class Index extends Action
{
    /**
     * This method is used to load layout and render information
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_view->loadLayout();
        $this->_view->renderLayout();
    }
}
