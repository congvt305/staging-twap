<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;

class MassEnable extends Popup
{
    /**
     * Execute
     *
     * @return ResponseInterface|ResultInterface
     */
    public function execute()
    {
        $this->_initWidgetInstance();
        try {
            $collection = $this->filter->getCollection($this->popupCollectionFactory->create());
            $count = 0;
            foreach ($collection as $item) {
                $item->setPopupStatus(1);
                $this->popupResources->save($item);
                $count++;
            }

            /* Invalidate Full Page Cache */
            $this->cache->invalidate('full_page');
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been enabled.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e);
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
