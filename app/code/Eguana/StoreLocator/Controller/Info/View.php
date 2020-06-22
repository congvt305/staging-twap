<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 1/1/20
 * Time: 11:39 AM
 */

namespace Eguana\StoreLocator\Controller\Info;

use Eguana\StoreLocator\Controller\Info\AbstractStores;

/**
 * View indivdual store
 * Class View
 */
class View extends AbstractStores
{
    /**
     * controller execute method
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\ResultInterface|\Magento\Framework\View\Result\Page
     */
    public function execute()
    {
        if ($this->_storesHelper->getStoresEnabled() != true) {
            $this->_redirect('');
        }
        return $this->_resultPageFactory->create();
    }
}
