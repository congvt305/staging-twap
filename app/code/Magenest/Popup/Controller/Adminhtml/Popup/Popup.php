<?php
namespace Magenest\Popup\Controller\Adminhtml\Popup;

use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magenest\Popup\Model\ResourceModel\Template;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Backend\App\Action\Context;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Math\Random;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Translate\InlineInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Ui\Component\MassAction\Filter;
use Magento\Widget\Controller\Adminhtml\Widget\Instance;
use Magento\Widget\Model\Widget\InstanceFactory;
use Psr\Log\LoggerInterface;

abstract class Popup extends Instance
{
    /** @var  PopupFactory */
    protected $_popupFactory;

    /** @var  TemplateFactory */
    protected $_popupTemplateFactory;

    /** @var  LoggerInterface */
    protected $_logger;

    /** @var  Registry */
    protected $_coreRegistry;

    /** @var DateTime */
    protected $_dateTime;

    /** @var TypeListInterface */
    protected $cache;

    /** @var  CollectionFactory */
    protected $popupCollectionFactory;

    /** @var \Magenest\Popup\Model\ResourceModel\Popup */
    protected $popupResources;

    /** @var  Filter */
    protected $filter;

    /** @var ResourceConnection */
    protected $resourceConnection;

    /** @var  PageFactory */
    protected $resultPageFactory;

    /** @var Json */
    protected $json;

    /** @var Template */
    protected $popupTemplateResources;

    /**
     * Popup constructor.
     * @param Context $context
     * @param Registry $coreRegistry
     * @param PopupFactory $popupFactory
     * @param TemplateFactory $popupTemplateFactory
     * @param InstanceFactory $widgetFactory
     * @param LoggerInterface $logger
     * @param Random $mathRandom
     * @param TypeListInterface $cache
     * @param DateTime $dateTime
     * @param InlineInterface $translateInline
     * @param Filter $filter
     * @param CollectionFactory $popupCollectionFactory
     * @param \Magenest\Popup\Model\ResourceModel\Popup $popupResources
     * @param ResourceConnection $resourceConnection
     * @param PageFactory $resultPageFactory
     * @param Json $json
     * @param Template $popupTemplateResources
     */
    public function __construct(
        Context $context,
        Registry $coreRegistry,
        PopupFactory $popupFactory,
        TemplateFactory $popupTemplateFactory,
        InstanceFactory $widgetFactory,
        LoggerInterface $logger,
        Random $mathRandom,
        TypeListInterface $cache,
        DateTime $dateTime,
        InlineInterface $translateInline,
        Filter $filter,
        CollectionFactory $popupCollectionFactory,
        \Magenest\Popup\Model\ResourceModel\Popup $popupResources,
        ResourceConnection $resourceConnection,
        PageFactory $resultPageFactory,
        Json $json,
        Template $popupTemplateResources
    ) {
        $this->_popupFactory = $popupFactory;
        $this->_popupTemplateFactory = $popupTemplateFactory;
        $this->_logger = $logger;
        $this->_coreRegistry = $coreRegistry;
        $this->_dateTime = $dateTime;
        $this->cache = $cache;
        $this->popupCollectionFactory = $popupCollectionFactory;
        $this->popupResources = $popupResources;
        $this->filter = $filter;
        $this->resourceConnection = $resourceConnection;
        $this->json = $json;
        $this->resultPageFactory = $resultPageFactory;
        $this->popupTemplateResources = $popupTemplateResources;
        parent::__construct($context, $coreRegistry, $widgetFactory, $logger, $mathRandom, $translateInline);
    }

    /**
     * Validate Date
     *
     * @param string $from
     * @param string $to
     * @return false|\Magento\Framework\Phrase
     */
    public function validDateFromTo($from, $to)
    {
        if ($from == '' || $to == '') {
            return false;
        } else {
            $timestampFrom = $this->_dateTime->timestamp($from);
            $timestampTo = $this->_dateTime->timestamp($to);
            if ($timestampFrom > $timestampTo) {
                return __('Start Date must not be later than End Date');
            } else {
                return false;
            }
        }
    }

    /**
     * Get ACL
     *
     * @return bool
     */
    public function _isAllowed()
    {
        return $this->_authorization->isAllowed('Magenest_Popup::popup');
    }
}
