<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class MassDelete extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->popupCollectionFactory->create());
            $count = 0;
            foreach ($collection as $item) {
                $this->popupResources->delete($item);
                $count++;
            }

            /* Invalidate Full Page Cache */
            $this->cache->invalidate('full_page');
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
