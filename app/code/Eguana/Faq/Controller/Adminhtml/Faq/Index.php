<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Controller\Adminhtml\Faq;

use Magento\Backend\App\Action;

/**
 * Class Index
 *
 * Eguana\Faq\Controller\Adminhtml\Faq
 */
class Index extends Action
{
    /**
     * Execute method
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|void
     */
    public function execute()
    {
        $this->_forward('grid');
    }
}
