<?php
namespace Magenest\Popup\Controller\Popup;

use Magenest\Popup\Helper\Cookie;
use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup as PopupResources;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Result\PageFactory;

/**
 * Class Popup
 * @package Magenest\Popup\Controller\Popup
 */
abstract class Popup extends Action
{
    /** @var PopupFactory */
    protected $_popupFactory;

    /** @var Cookie */
    protected $_cookieHelper;

    /** @var DateTime */
    protected $_dateTime;

    /** @var Data */
    protected $_dataHelper;

    /** @var PageFactory */
    protected $resultPageFactory;

    /** @var Json */
    protected $json;

    /** @var PopupResources */
    protected $popupResources;

    /**
     * Popup constructor.
     * @param PopupFactory $popupFactory
     * @param PopupResources $popupResources
     * @param Cookie $cookieHelper
     * @param Data $dataHelper
     * @param DateTime $dateTime
     * @param PageFactory $resultPageFactory
     * @param Context $context
     * @param Json $json
     */
    public function __construct(
        PopupFactory $popupFactory,
        PopupResources $popupResources,
        Cookie $cookieHelper,
        Data $dataHelper,
        DateTime $dateTime,
        PageFactory $resultPageFactory,
        Context $context,
        Json $json
    ) {
        $this->_popupFactory = $popupFactory;
        $this->popupResources = $popupResources;
        $this->_cookieHelper = $cookieHelper;
        $this->_dataHelper = $dataHelper;
        $this->resultPageFactory = $resultPageFactory;
        $this->_dateTime = $dateTime;
        $this->json = $json;
        parent::__construct($context);
    }
}
