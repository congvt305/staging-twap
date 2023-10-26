<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magenest\Popup\Model\TemplateFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Edit extends Action
{
    /** @var PageFactory */
    protected $_resultPageFactory;

    /** @var Registry */
    protected $_coreRegistry;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var TemplateFactory */
    protected $_popupTemplatesFactory;

    /** @var \Magenest\Popup\Model\ResourceModel\Template */
    private $popupTemplateResources;

    /**
     * Edit constructor.
     * @param PageFactory $resultPageFactory
     * @param Registry $coreRegistry
     * @param LoggerInterface $logger
     * @param TemplateFactory $popupTemplatesFactory
     * @param \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources
     * @param Context $context
     */
    public function __construct(
        PageFactory                                  $resultPageFactory,
        Registry                                     $coreRegistry,
        LoggerInterface                              $logger,
        TemplateFactory                              $popupTemplatesFactory,
        \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources,
        Context                                      $context
    ) {
        $this->_resultPageFactory     = $resultPageFactory;
        $this->_coreRegistry          = $coreRegistry;
        $this->_logger                = $logger;
        $this->_popupTemplatesFactory = $popupTemplatesFactory;
        $this->popupTemplateResources = $popupTemplateResources;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $popupTemplate = $this->_popupTemplatesFactory->create();
        try {
            $template_id = $this->_request->getParam('id');
            if ($template_id) {
                $this->popupTemplateResources->load($popupTemplate, $template_id);
                if (!$popupTemplate->getTemplateId()) {
                    $this->messageManager->addErrorMessage(__('This Popup Template doesn\'t exist'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e);
        }
        $this->_coreRegistry->register('popup_template', $popupTemplate);

        $resultPage = $this->_resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(
            $popupTemplate->getTemplateId() ? __('Edit Popup Template') : __('New Popup Template')
        );
        return $resultPage;
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
