<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 4:55 AM
 */
namespace Eguana\Magazine\Controller\Adminhtml\Magazine;

use Eguana\Magazine\Controller\Adminhtml\AbstractController;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;

/**
 * Action for listing magazine
 * Class Grid
 */
class Grid extends AbstractController
{

    /**
     * Execute the listing action
     * @return ResponseInterfaceAlias|ResultInterfaceAlias|mixed
     */
    public function execute()
    {
        $resultPage = $this->_init($this->resultPageFactory->create());
        return $resultPage;
    }
}
