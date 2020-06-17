<?php
namespace Eguana\StoreLocator\Controller\Adminhtml\Grid;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\ResultInterface;
use Symfony\Component\Config\Definition\Exception\Exception;
use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;

/**
 * Class inline edit
 *
 * Class InlineEdit
 *  Eguana\StoreLocator\Controller\Adminhtml\Grid
 */
class InlineEdit extends AbstractStores
{

    /**
     * controller action method
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $resultJson = $this->_resultJsonFactory->create();
        $error = false;
        $messages = [];
        if ($this->getRequest()->getParam('isAjax')) {
            $postItems = $this->getRequest()->getParam('items', []);
            if (!count($postItems)) {
                $messages[] = __('Please correct the data sent.');
                $error = true;
            } else {
                $this->_logger->alert(var_export($postItems, true));
                foreach (array_keys($postItems) as $storeInfoId) {
                    $storeModel = $this->_storeInfoRepo->getById($storeInfoId);
                    unset($postItems[$storeInfoId]['entity_id']);
                    $storeModel->addData($postItems[$storeInfoId]);
                    try {
                        $this->_storeInfoRepo->save($storeModel);
                    } catch (Exception $e) {
                        $messages[] = $e->getMessage();
                        $error = true;
                    }
                }
            }
        }

        return $resultJson->setData([
            'messages' => $messages,
            'error' => $error
        ]);
    }
}
