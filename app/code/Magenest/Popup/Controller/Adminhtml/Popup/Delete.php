<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultInterface;

class Delete extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        $params = $this->_request->getParams();
        $connection = $this->resourceConnection->getConnection();
        $popupLayoutTable = $this->resourceConnection->getTableName('magenest_popup_layout');
        $popupId = $params['id'] ?? '';
        $select = $connection->select()->from($popupLayoutTable, 'layout_update_id')->where('popup_id = ?', $popupId);
        $removeLayoutUpdateIds = $connection->fetchCol($select);

        try {
            $popupModel = $this->_popupFactory->create();
            if ($popupId) {
                $this->popupResources->load($popupModel, $popupId);
                $this->popupResources->delete($popupModel);
                if (!empty($removeLayoutUpdateIds)) {
                    $inCond = $connection->prepareSqlCondition('popup_id', $popupId);
                    $connection->delete($popupLayoutTable, $inCond);
                    $inCond = $connection->prepareSqlCondition('layout_update_id', ['in' => $removeLayoutUpdateIds]);
                    $connection->delete($this->resourceConnection->getTableName('layout_update'), $inCond);
                }
            }

            /* Invalidate Full Page Cache */
            $this->cache->invalidate('full_page');
            $this->messageManager->addSuccessMessage(__('The Popup has been deleted.'));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e);
        }
        $this->_redirect('*/*/index');
    }
}
