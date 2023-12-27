<?php

namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\ResourceModel\Template\CollectionFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Psr\Log\LoggerInterface;

class LoadDefault extends \Magento\Backend\App\Action
{
    /** @var Data */
    protected $_helperData;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var CollectionFactory */
    private $popupTemplateCollection;

    /** @var \Magenest\Popup\Model\ResourceModel\Template */
    private $popupTemplateResources;

    /**
     * LoadDefault constructor.
     * @param Data $helperData
     * @param \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources
     * @param CollectionFactory $popupTemplateCollection
     * @param LoggerInterface $logger
     * @param Context $context
     */
    public function __construct(
        Data $helperData,
        \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources,
        CollectionFactory $popupTemplateCollection,
        LoggerInterface $logger,
        Context $context
    ) {
        $this->_helperData = $helperData;
        $this->popupTemplateResources = $popupTemplateResources;
        $this->popupTemplateCollection = $popupTemplateCollection;
        $this->_logger = $logger;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface
     */
    public function execute()
    {
        try {
            $popup_type = $this->_helperData->getPopupTemplateDefault();
            $count = 0;

            $data = [];
            $popupTemplate = $this->popupTemplateCollection->create()
                ->addFieldToFilter('status', ['eq' => 1])
                ->addFieldToSelect('class')
                ->getData();
            $templateClass = array_column($popupTemplate, 'class');
            foreach ($popup_type as $type) {
                if (!in_array($type['class'], $templateClass)) {
                    $data[] = [
                        'template_name' => $type['name'],
                        'template_type' => $type['type'],
                        'html_content' => $this->_helperData->getTemplateDefault($type['path']),
                        'css_style' => '',
                        'class' => $type['class'],
                        'status' => 1
                    ];
                    $count++;
                }
            }

            if (!empty($data)) {
                $this->popupTemplateResources->insertMultiple($data);
            }
            $this->messageManager->addSuccessMessage(__('A total of %1 record(s) have been inserted.', $count));
        } catch (\Exception $e) {
            $this->_logger->critical($e);
            $this->messageManager->addErrorMessage($e->getMessage());
        }
        return $this->resultFactory->create(ResultFactory::TYPE_REDIRECT)->setPath('*/*/index');
    }

    /**
     * Get ACL
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Popup::template');
    }
}
