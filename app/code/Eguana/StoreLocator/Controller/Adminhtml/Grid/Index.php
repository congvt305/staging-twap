<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-02
 * Time: 오전 10:34
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Grid;

use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * show gird
 *
 * Class Index
 *  Eguana\StoreLocator\Controller\Adminhtml\Grid
 */
class Index extends AbstractStores
{
    /**
     * controller execute method
     * @return ResponseInterface|ResultInterface|Page|mixed
     */
    public function execute()
    {
        $result = $this->_resultPageFactory->create();
        $result = $this->initPage($result);
        return $result;
    }
}
