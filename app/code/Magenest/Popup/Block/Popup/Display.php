<?php
namespace Magenest\Popup\Block\Popup;

use Magenest\Popup\Helper\Cookie;
use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\Popup;
use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Display extends Template
{
    /** @var Data */
    protected $_helperData;

    /** @var  PopupFactory */
    protected $_popupFactory;

    /** @var  TemplateFactory */
    protected $_templateFactory;

    /** @var FilterProvider */
    protected $_filterProvider;

    /** @var CookieManagerInterface */
    protected $_cookieManager;

    /** @var  Cookie */
    protected $_helperCookie;

    /** @var DateTime */
    protected $_dateTime;

    /** @var ResourceConnection  */
    protected $_resourceConnection;

    /** @var StoreManagerInterface  */
    protected $_storeManager;

    /** @var Session */
    protected $_customerSession;

    /** @var Json */
    protected $_json;

    /** @var CollectionFactory */
    private $popupCollection;

    /**
     * Display constructor.
     * @param Data $helperData
     * @param PopupFactory $popupFactory
     * @param CollectionFactory $popupCollection
     * @param TemplateFactory $templateFactory
     * @param Cookie $helperCookie
     * @param Session $customerSession
     * @param FilterProvider $filterProvider
     * @param CookieManagerInterface $cookieManager
     * @param DateTime $dateTime
     * @param Context $context
     * @param ResourceConnection $resourceConnection
     * @param StoreManagerInterface $storeManager
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        Data $helperData,
        PopupFactory $popupFactory,
        CollectionFactory $popupCollection,
        TemplateFactory $templateFactory,
        Cookie $helperCookie,
        Session $customerSession,
        FilterProvider $filterProvider,
        CookieManagerInterface $cookieManager,
        DateTime $dateTime,
        Context $context,
        ResourceConnection $resourceConnection,
        StoreManagerInterface $storeManager,
        Json $json,
        array $data = []
    ) {
        $this->_helperData = $helperData;
        $this->_popupFactory = $popupFactory;
        $this->popupCollection = $popupCollection;
        $this->_templateFactory = $templateFactory;
        $this->_filterProvider = $filterProvider;
        $this->_cookieManager = $cookieManager;
        $this->_helperCookie = $helperCookie;
        $this->_dateTime = $dateTime;
        $this->_resourceConnection = $resourceConnection;
        $this->_storeManager = $storeManager;
        $this->_customerSession = $customerSession;
        $this->_json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Check Customer Group
     *
     * @return bool
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkCustomerGroup()
    {
        $customerGroupIds = explode(',', $this->getPopup()->getCustomerGroupIds() ?? "");
        $customerGroup = $this->_customerSession->getCustomerGroupId();
        return in_array($customerGroup, $customerGroupIds)
            || in_array(GroupInterface::CUST_GROUP_ALL, $customerGroupIds);
    }

    /**
     * Check Page to Show
     *
     * @return bool
     */
    public function checkPageToShow()
    {
        if ($this->_helperData->isModuleEnable()) {
            return true;
        }
        return false;
    }

    /**
     * Get Data Display
     *
     * @return false|string
     * @throws \Exception
     */
    public function getDataDisplay()
    {
        /** @var \Magenest\Popup\Model\Popup $popup */
        $popup = $this->getPopup();
        $data = $popup->getData();
        $data['class'] = $this->getTemplateClassDefault($popup->getPopupTemplateId());
        $data['url_check_cookie'] = $this->getUrlCheckCookie();
        $data['url_close_popup'] = $this->getUrlClosePopup();
        $data['lifetime'] = $this->getCookieLifeTime();
        if (isset($data['background_image'])) {
            $imageArr = (array)$this->_json->unserialize($data['background_image']);
            $background_image = (array)reset($imageArr);
            $styleExtend = '.magenest-popup-inner{background-image: url(' . $background_image['url'] . ') !important;}';
            $data['css_style'] .= $styleExtend;
        }
        return json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Get Popup
     *
     * @return \Magenest\Popup\Model\Popup|string
     * @throws \Exception
     */
    public function getPopup()
    {
        $popupIdCookies = $this->getCookie() == null ? [] : $this->getCookie();
        $today = $this->_dateTime->date('Y-m-d');
        $timestamp_today = $this->_dateTime->timestamp($today);
        $popupIdArray = $this->getPopupIdArray();
        $storeId = $this->getStoreId();
        $data = [];
        if (!empty($popupIdArray)) {
            $popupCollections = $this->popupCollection->create()
                ->addFieldToFilter('popup_id', ['in', [$popupIdArray]])
                ->addFieldToFilter('visible_stores', ['in', [0,$storeId]]);
            foreach ($popupCollections as $popupCollection) {
                $start_date = $popupCollection->getStartDate();
                $end_date = $popupCollection->getEndDate();
                if ($start_date == '' && $end_date == '') {
                    $data[] = $popupCollection;
                } elseif ($start_date == '' && $end_date != '') {
                    $end_date_timestamp = $this->_dateTime->timestamp($end_date);
                    if ($end_date_timestamp >= $timestamp_today) {
                        $data[] = $popupCollection;
                    }
                } elseif ($start_date != '' && $end_date == '') {
                    $start_date_timestamp = $this->_dateTime->timestamp($start_date);
                    if ($start_date_timestamp <= $timestamp_today) {
                        $data[] = $popupCollection;
                    }
                } elseif ($start_date != '' && $end_date != '') {
                    $start_date_timestamp = $this->_dateTime->timestamp($start_date);
                    $end_date_timestamp = $this->_dateTime->timestamp($end_date);
                    if ($start_date_timestamp <= $timestamp_today && $end_date_timestamp >= $timestamp_today) {
                        $data[] = $popupCollection;
                    }
                }
            }
        }
        $popupModel = '';
        if (!empty($data)) {
            $min = null;
            /** @var \Magenest\Popup\Model\Popup $popup */
            foreach ($data as $popup) {
                $priority = $popup->getPriority();
                $min = $min == null ? $priority : $min;
                $popupModel = $min >= $priority ? $popup : $popupModel;
            }
        }

        if ($popupModel instanceof \Magenest\Popup\Model\Popup) {
            $html_content = $popupModel->getHtmlContent();
            if (isset($html_content) && is_string($html_content)) {
                $content = $this->_filterProvider->getBlockFilter()->filter($html_content);
                $content .= '<span id="copyright"></span>';
                $content = "<div class='magenest-popup-inner'>" . $content . "</div>";
            } else {
                $content = "";
            }
            $popupModel->setHtmlContent($content);
        } else {
            $popupModel = $this->_popupFactory->create();
        }
        return $popupModel;
    }

    /**
     * Get Current Full Action Name
     *
     * @return mixed
     */
    public function getCurrentFullActionName()
    {
        return $this->getRequest()->getFullActionName();
    }

    /**
     * Is Preview?
     *
     * @return bool
     */
    public function isPreview()
    {
        $fullActionName = $this->getCurrentFullActionName();
        return in_array($fullActionName, ["magenest_popup_popup_preview", "magenest_popup_template_preview"])
            && ($this->getRequest()->getParam('popup_id') != '' || $this->getRequest()->getParam('id') != '')
            || $this->getRequest()->getParam('html_content');
    }

    /**
     * Get Template Class Default
     *
     * @param string $templateId
     * @return array|mixed|string|null
     */
    public function getTemplateClassDefault($templateId)
    {
        $templateModel = $this->_templateFactory->create()->load($templateId);
        if ($templateModel->getTemplateId()) {
            return $templateModel->getData('class');
        } else {
            return 'popup-default-1';
        }
    }

    /**
     * Get Check Cookies URL
     *
     * @return string
     */
    public function getUrlCheckCookie()
    {
        return $this->getUrl('magenest_popup/popup/checkCookie');
    }

    /**
     * Get Close Popup URL
     *
     * @return string
     */
    public function getUrlClosePopup()
    {
        return $this->getUrl('magenest_popup/popup/closePopup');
    }

    /**
     * Get Cookie
     *
     * @return array|null
     */
    public function getCookie()
    {
        $cookies = $this->_helperCookie->get();
        if ($cookies) {
            $cookieArr = $this->_json->unserialize($cookies ?? 'null');
            $popupIds = [];
            $i = 0;
            foreach ($cookieArr as $key => $value) {
                if ($key == 'view_page') {
                    $i++;
                    continue;
                }
                $popupIds[] = [
                    'popup_id' => $key,
                    'timestamp' => $value
                ];
            }
            return $popupIds;
        } else {
            return null;
        }
    }

    /**
     * Get Cookie Lifetime
     *
     * @return int
     */
    public function getCookieLifeTime()
    {
        /** @var \Magenest\Popup\Model\Popup $collection */
        $collection = $this->popupCollection->create()
            ->addFieldToFilter('enable_cookie_lifetime', 1)
            ->setOrder('cookie_lifetime', 'DESC')
            ->getFirstItem();
        return $collection->getCookieLifetime() ?? 86400;
    }

    /**
     * Get Popup IDs
     *
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getPopupIdArray()
    {
        $layout = $this->getLayout()->getUpdate()->getHandles();
        $connection = $this->_resourceConnection->getConnection();
        $layoutUpdateTable = $this->_resourceConnection->getTableName('layout_update');
        $select = $connection->select()->from($layoutUpdateTable, 'layout_update_id')
            ->where('handle IN (?)', $layout);
        $layout_update_id = $connection->fetchCol($select);
        $popupLayoutTable = $this->_resourceConnection->getTableName('magenest_popup_layout');
        $select = $connection->select()->from($popupLayoutTable, 'popup_id')
            ->where('layout_update_id IN (?)', $layout_update_id);
        return $connection->fetchCol($select);
    }

    /**
     * Get Home URL
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getHomeUrl()
    {
        return $this->_storeManager->getStore()->getBaseUrl();
    }

    /**
     * Get Contact URL
     *
     * @return string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getContactUrl()
    {
        return $this->_storeManager->getStore()->getUrl('contact');
    }

    /**
     * Get Store ID
     *
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get Visible Store
     *
     * @param Popup $popup
     * @return array|false|string[]
     */
    public function getVisibleStore($popup)
    {
        if (!empty($popup)) {
            $visibleStore = $popup->getVisibleStores();
            $storeIds = str_replace(',', ' ', $visibleStore);
            return explode(' ', $storeIds);
        } else {
            return [];
        }
    }

    /**
     * Check Store Popup
     *
     * @param Popup $popup
     * @return bool
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function checkStorePopup($popup)
    {
        $storeId = $this->getStoreId();
        $visibleStore = $this->getVisibleStore($popup);
        return in_array("0", $visibleStore) || in_array($storeId, $visibleStore);
    }

    /**
     * Check Single Store Mode
     *
     * @return bool
     */
    public function enableSingleStoreMode()
    {
        return $this->_storeManager->isSingleStoreMode();
    }
}
