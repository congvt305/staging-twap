<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;

class MassDelete extends Template
{
    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $collection = $this->filter->getCollection($this->popupTemplateCollection->create());
            $count = 0;
            $templateIds = [];
            /** @var \Magenest\Popup\Model\Template $item */
            foreach ($collection->getItems() as $item) {
                if ($this->getPopupsByTemplateId($item->getTemplateId())) {
                    throw new LocalizedException(__(
                        '%1 is currently being used for a popup. '
                        . 'Please remove a template from all popups before deleting it.',
                        $item->getTemplateName()
                    ));
                }
                $templateIds[] = $item->getTemplateId();
                $count++;
            }

            if (!empty($templateIds)) {
                $this->popupTemplateResources->deleteMultiple(implode(", ", $templateIds));
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been deleted.', $count));
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }
}
