<?php
declare(strict_types=1);

namespace CJ\Popup\Block\Popup;

use Magenest\Popup\Helper\Cookie;
use Magenest\Popup\Helper\Data;
use Magenest\Popup\Model\PopupFactory;
use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Customer\Model\Session;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\View\Element\Template\Context;
use Magento\Store\Model\StoreManagerInterface;

class Display extends \Magenest\Popup\Block\Popup\Display
{
    /** @var CollectionFactory */
    private $popupCollection;

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
        $this->popupCollection = $popupCollection;
        parent::__construct(
            $helperData,
            $popupFactory,
            $popupCollection,
            $templateFactory,
            $helperCookie,
            $customerSession,
            $filterProvider,
            $cookieManager,
            $dateTime,
            $context,
            $resourceConnection,
            $storeManager,
            $json,
            $data
        );
    }

    /**
     * Get popup data
     *
     * @param \Magenest\Popup\Model\Popup $popup
     * @return \Magenest\Popup\Model\Popup
     * @throws \Exception
     */
    public function getDataDisplayPopup($popup)
    {
        if ($popup instanceof \Magenest\Popup\Model\Popup) {
            $html_content = $popup->getHtmlContent();
            if (isset($html_content) && is_string($html_content)) {
                $content = $this->_filterProvider->getBlockFilter()->filter($html_content);
                $content .= '<span id="copyright"></span>';
                $content = "<div class='magenest-popup-inner'>" . $content . "</div>";
            } else {
                $content = "";
            }
            $popup->setHtmlContent($content);
        } else {
            $popup = $this->_popupFactory->create();
        }
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
     * @return array
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getAllPopUpData()
    {
        $today = $this->_dateTime->date('Y-m-d');
        $timestamp_today = $this->_dateTime->timestamp($today);
        $popupIdArray = $this->getPopupIdArray();
        $storeId = $this->getStoreId();
        $data = [];
        if (!empty($popupIdArray)) {
            $popupCollections = $this->popupCollection->create()
                ->addFieldToFilter('popup_id', ['in', [$popupIdArray]])
                ->addFieldToFilter('visible_stores', ['in', [0,$storeId]])
                ->setOrder('priority', 'DESC');
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
        return $data;
    }
}
