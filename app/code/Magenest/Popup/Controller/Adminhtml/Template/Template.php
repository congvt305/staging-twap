<?php
namespace Magenest\Popup\Controller\Adminhtml\Template;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup\Collection;
use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Framework\Registry;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * Class Template
 * @package Magenest\Popup\Controller\Adminhtml\Template
 */
abstract class Template extends Action
{
    /** @var  TemplateFactory */
    protected $_popupTemplateFactory;

    /** @var  LoggerInterface */
    protected $_logger;

    /** @var  Registry */
    protected $_coreRegistry;

    /** @var PageFactory */
    protected $_resultPageFactory;

    /** @var CollectionFactory */
    private $popupCollectionFactory;

    /** @var Collection|null */
    private $popupCollection = null;

    /** @var array */
    private $cachedPopups = [];

    /** @var \Magenest\Popup\Model\ResourceModel\Template */
    protected $popupTemplateResources;

    /** @var Filter */
    protected $filter;

    /** @var \Magenest\Popup\Model\ResourceModel\Template\CollectionFactory */
    protected $popupTemplateCollection;

    /**
     * Template constructor.
     * @param CollectionFactory $popupCollectionFactory
     * @param TemplateFactory $popupTemplateFactory
     * @param \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources
     * @param \Magenest\Popup\Model\ResourceModel\Template\CollectionFactory $popupTemplateCollection
     * @param LoggerInterface $logger
     * @param Registry $coreRegistry
     * @param Context $context
     * @param PageFactory $resultPageFactory
     * @param Filter $filter
     */
    public function __construct(
        CollectionFactory $popupCollectionFactory,
        TemplateFactory $popupTemplateFactory,
        \Magenest\Popup\Model\ResourceModel\Template $popupTemplateResources,
        \Magenest\Popup\Model\ResourceModel\Template\CollectionFactory $popupTemplateCollection,
        LoggerInterface $logger,
        Registry $coreRegistry,
        Context $context,
        PageFactory $resultPageFactory,
        Filter $filter
    ) {
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->_popupTemplateFactory = $popupTemplateFactory;
        $this->popupTemplateResources = $popupTemplateResources;
        $this->popupTemplateCollection = $popupTemplateCollection;
        $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_resultPageFactory = $resultPageFactory;
        $this->filter = $filter;
        parent::__construct($context);
    }

    /**
     * Get Popup Collection
     *
     * @return Collection
     */
    private function getPopupCollection()
    {
        if ($this->popupCollection === null) {
            $this->popupCollection = $this->popupCollectionFactory->create();
        }

        return $this->popupCollection->reset();
    }

    /**
     * Get Popups by Template ID
     *
     * @param string $templateId
     * @return bool
     */
    public function getPopupsByTemplateId($templateId)
    {
        if (!isset($this->cachedPopups[$templateId])) {
            try {
                $popup = $this->getPopupCollection()
                    ->addFieldToFilter('popup_template_id', $templateId)
                    ->setPageSize(1)->setCurPage(1)
                    ->getFirstItem()->getPopupId();
                $this->cachedPopups[$templateId] = !empty($popup);
            } catch (\Exception $e) {
                $this->_logger->critical($e);
                $this->cachedPopups[$templateId] = false;
            }
        }

        return $this->cachedPopups[$templateId];
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
