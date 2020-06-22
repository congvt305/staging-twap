<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-05
 * Time: 오전 9:49
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Edit;

use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\View\Result\Page;

/**
 * Edit form class
 *
 * Class Form
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class Form extends AbstractStores
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
