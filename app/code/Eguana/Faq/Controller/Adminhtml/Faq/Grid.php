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

use Eguana\Faq\Controller\Adminhtml\AbstractController;

/**
 * Class Grid
 *
 * Eguana\Faq\Controller\Adminhtml\Faq
 */
class Grid extends AbstractController
{

    /**
     * Execute method
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $resultPage = $this->_init($this->resultPageFactory->create());
        return $resultPage;
    }
}
