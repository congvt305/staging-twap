<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-07-25
 * Time: 오후 2:12
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Edit;

use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\DataObject;

/**
 * validate class
 *
 * Class Validate
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class Validate extends AbstractStores
{
    /**
     * Forward to edit
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $response = new DataObject();
        $response->setError(0);
        $resultJson = $this->_resultJsonFactory->create();
        if ($response->getError()) {
            $response->setError(true);
            $response->setMessages($response->getMessages());
        }
        $resultJson->setData($response);
        return $resultJson;
    }
}
