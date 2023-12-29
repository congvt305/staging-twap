<?php
namespace Magenest\Popup\Controller\Popup;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup as PopupResources;
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

    /** @var PopupFactory */
    protected $_popupFactory;

    /** @var LoggerInterface */
    protected $_logger;

    /** @var Registry */
    protected $_coreRegistry;

    /** @var PopupResources */
    private $popupResources;

    /**
     * Preview constructor.
     * @param PopupFactory $popupFactory
     * @param PopupResources $popupResources
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param PageFactory $resultPageFactory
     * @param Context $context
     */
    public function __construct(
        PopupFactory $popupFactory,
        PopupResources $popupResources,
        LoggerInterface $logger,
        Registry $coreRegistry,
        PageFactory $resultPageFactory,
        Context $context
    ) {
        $this->_popupFactory = $popupFactory;
        $this->popupResources = $popupResources;
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
        $popupModel = $this->_popupFactory->create();
        try {
            $popupId = $this->_request->getParam('popup_id');
            $htmlContent = $this->_request->getParam('html_content');
            $templateId = $this->_request->getParam('template_id');
            $backgroundImage = $this->_request->getParam('background_image');
            if ($htmlContent) {
                $this->_coreRegistry->register('html_content', $htmlContent);
            }
            if ($templateId) {
                $this->_coreRegistry->register('template_id', $templateId);
            }
            $this->_coreRegistry->register('background_image', $backgroundImage);
            if ($popupId) {
                $this->popupResources->load($popupModel, $popupId);
                if (!$popupModel->getPopupId()) {
                    $this->messageManager->addErrorMessage(__('This Popup doesn\'t exist'));
                    $resultRedirect = $this->resultRedirectFactory->create();
                    return $resultRedirect->setPath('*/*/index');
                }
            }
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
            $this->_logger->critical($e);
        }

        $this->_coreRegistry->register('popup', $popupModel);
        $resultPage = $this->resultPageFactory->create();
        $resultPage->getConfig()->getTitle()->prepend(__('Preview Popup'));
        return $resultPage;
    }
}
