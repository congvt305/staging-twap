<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magenest\Popup\Model\TemplateFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\Controller\ResultInterface;

class LoadTemplate extends Action
{
    /** @var \Magenest\Popup\Model\ResourceModel\Template */
    protected $_templateResource;

    /** @var JsonFactory */
    protected $_jsonResult;

    /** @var TemplateFactory */
    protected $_templateModel;

    /**
     * LoadTemplate constructor.
     * @param JsonFactory $jsonResult
     * @param \Magenest\Popup\Model\ResourceModel\Template $templateResource
     * @param TemplateFactory $templateModel
     * @param Context $context
     */
    public function __construct(
        JsonFactory $jsonResult,
        \Magenest\Popup\Model\ResourceModel\Template $templateResource,
        TemplateFactory $templateModel,
        Context $context
    ) {
        $this->_jsonResult = $jsonResult;
        $this->_templateModel = $templateModel;
        $this->_templateResource = $templateResource;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $templateId = $this->_request->getParam('template_id');
        $template = $this->_templateModel->create();
        $this->_templateResource->load($template, $templateId);
        return $this->_jsonResult->create()->setData($template->getHtmlContent());
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
