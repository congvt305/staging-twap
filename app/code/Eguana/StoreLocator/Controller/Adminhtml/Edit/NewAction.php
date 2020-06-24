<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-09-05
 * Time: 오전 9:49
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Edit;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use Magento\Framework\Controller\ResultInterface;

/**
 * Add new action
 *
 * Class NewAction
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class NewAction extends AbstractStores
{
    /**
     * controller execute method
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $resultForward = $this->resultFactory->create(ResultFactory::TYPE_FORWARD);
        return $resultForward->forward('form');
    }
}
