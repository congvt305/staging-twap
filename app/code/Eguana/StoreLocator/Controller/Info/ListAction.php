<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-21
 * Time: 오후 4:05
 */

namespace Eguana\StoreLocator\Controller\Info;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Loading list action
 *
 * Class ListAction
 *  Eguana\StoreLocator\Controller\Info
 */
class ListAction extends AbstractStores
{

    /**
     * Function execute method will load page
     * @return ResponseInterface|ResultInterface|Page
     */
    public function execute()
    {
        if ($this->_storesHelper->getStoresEnabled() != true) {
            $this->_redirect('');
        }

        return $this->_resultPageFactory->create();
    }
}
