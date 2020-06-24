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
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

/**
 * For edit existing record
 *
 * Class IndexAction
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class IndexAction extends AbstractStores
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
