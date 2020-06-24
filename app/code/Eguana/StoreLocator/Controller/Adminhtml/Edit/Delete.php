<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-07-25
 * Time: 오후 2:12
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Edit;

use Exception;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use RuntimeException;

/**
 * For deleting any store Record
 *
 * Class Delete
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class Delete extends AbstractStores
{
    /**
     * Save action
     *
     * @return ResultInterface
     */

    public function execute()
    {
        $storeInfoId = $this->getRequest()->getParam('entity_id');
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($storeInfoId) {
            try {
                $storeModel = $this->_storeInfoRepo->getById($storeInfoId);
                $this->_storeInfoResource->delete($storeModel);
                $this->messageManager->addSuccessMessage(__('You Delete this Store Information.'));
                $this->_session->setFormData(false);
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }
        }
        return $resultRedirect->setPath('*/grid/');
    }
}
