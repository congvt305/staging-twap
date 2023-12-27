<?php
namespace Magenest\Popup\Controller\Template;

use Magenest\Popup\Model\ResourceModel\Template;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Redirect;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\Page;
use Magento\Framework\View\Result\PageFactory;
use Psr\Log\LoggerInterface;

class Preview extends Action
{
    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var TemplateFactory */
    protected $_popupTemplateFactory;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var Registry */
    protected $_coreRegistry;

    /** @var Template */
    protected $popupTemplateResources;

    /**
     * Preview constructor.
     * @param TemplateFactory $popupTemplateFactory
     * @param Template $popupTemplateResources
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        TemplateFactory $popupTemplateFactory,
        Template $popupTemplateResources,
        LoggerInterface $logger,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->_popupTemplateFactory = $popupTemplateFactory;
        $this->popupTemplateResources = $popupTemplateResources;
        $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->resultPageFactory = $resultPageFactory;
        parent::__construct($context);
    }

    /**
     * Execute
     *
     * @return ResponseInterface|Redirect|ResultInterface|Page
     */
    public function execute()
    {
        $popupTemplateModel = $this->_popupTemplateFactory->create();
        try {
            $template_id = $this->_request->getParam('id');
            if ($template_id) {
                $this->popupTemplateResources->load($popupTemplateModel, $template_id);
                if (!$popupTemplateModel->getTemplateId()) {
                    $this->messageManager->addErrorMessage(__('This Popup Template doesn\'t exist'));
                    return $this->resultRedirectFactory->create()->setPath('*/*/index');
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e->getMessage());
        }
        $this->_coreRegistry->register('popup_template', $popupTemplateModel);

        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Preview Template'));
        return $resultPage;
    }
}
