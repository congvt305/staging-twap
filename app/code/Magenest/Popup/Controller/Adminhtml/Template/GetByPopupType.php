<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magenest\Popup\Model\ResourceModel\Template\CollectionFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class GetByPopupType extends Action
{
    /** @var CollectionFactory */
    protected $popupTemplateCollection;

    /** @var JsonFactory */
    protected $jsonResult;

    /**
     * GetByPopupType constructor.
     * @param JsonFactory $jsonResult
     * @param CollectionFactory $popupTemplateCollection
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonResult,
        CollectionFactory $popupTemplateCollection,
        Context $context
    ) {
        $this->jsonResult = $jsonResult;
        $this->popupTemplateCollection = $popupTemplateCollection;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = $this->jsonResult->create();
        $popupType = $this->_request->getParam('popup_type');
        $templateCollection = $this->popupTemplateCollection->create();
        if ($popupType) {
            $templateCollection->addFieldToFilter('template_type', $popupType);
        }
        $result->setData($templateCollection->toOptionArray());
        return $result;
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
