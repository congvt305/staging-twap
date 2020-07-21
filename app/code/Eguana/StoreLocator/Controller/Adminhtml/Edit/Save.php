<?php
/**
 * Created by PhpStorm.
 * User: jason
 * Date: 2016-07-25
 * Time: 오후 2:12
 */

namespace Eguana\StoreLocator\Controller\Adminhtml\Edit;

use Eguana\StoreLocator\Controller\Adminhtml\AbstractStores;
use Exception;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\FileSystemException;
use RuntimeException;

/**
 * Save store Information
 *
 * Class Save
 *  Eguana\StoreLocator\Controller\Adminhtml\Edit
 */
class Save extends AbstractStores
{
    /**
     * save action
     * @return ResponseInterface|Redirect|ResultInterface
     * @throws FileSystemException
     */
    public function execute()
    {
        $data = $this->getRequest()->getPostValue();
        $resultRedirect = $this->resultRedirectFactory->create();
        if ($data) {
            $storeInfoData = $data['storeinfo_data'];
            $this->_storeInfo->adminSaveDataBind($storeInfoData);
            try {
                $this->_storeInfoRepo->save($this->_storeInfo);
                $this->messageManager->addSuccessMessage(__('You saved this Store Information.'));
                $this->_session->setFormData(false);
                if ($this->getRequest()->getParam('back')) {
                    return $resultRedirect->setPath(
                        '*/*/form',
                        ['entity_id' => $this->_storeInfo->getId(), '_current' => true]
                    );
                }
                return $resultRedirect->setPath('*/grid/');
            } catch (RuntimeException $e) {
                $this->messageManager->addErrorMessage($e->getMessage());
            } catch (Exception $e) {
                $this->messageManager->addExceptionMessage($e, __($e->getMessage()));
            }

            $this->_getSession()->setFormData($data);
            return $resultRedirect->setPath(
                '*/*/form',
                ['entity_id' => $this->getRequest()->getParam('entity_id')]
            );
        }
        return $resultRedirect->setPath('*/grid/');
    }
}
