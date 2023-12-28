<?php
namespace Magenest\Popup\Block\Template;

use Magenest\Popup\Helper\Cookie;
use Magenest\Popup\Model\TemplateFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Framework\Registry;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;

class Preview extends Template
{
    /** @var  Registry */
    protected $_coreRegistry;

    /** @var  TemplateFactory */
    protected $_templateFactory;

    /** @var  Cookie */
    protected $_helperCookie;

    /** @var FilterProvider */
    protected $_filterProvider;

    /** @var Json */
    private $json;

    /**
     * Preview constructor.
     * @param FilterProvider $filterProvider
     * @param TemplateFactory $templateFactory
     * @param Cookie $helperCookie
     * @param Registry $coreRegistry
     * @param Context $context
     * @param Json $json
     * @param array $data
     */
    public function __construct(
        FilterProvider $filterProvider,
        TemplateFactory $templateFactory,
        Cookie $helperCookie,
        Registry $coreRegistry,
        Context $context,
        Json $json,
        array $data = []
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_templateFactory = $templateFactory;
        $this->_helperCookie = $helperCookie;
        $this->_coreRegistry = $coreRegistry;
        $this->json = $json;
        parent::__construct($context, $data);
    }

    /**
     * Get Data Display
     *
     * @return false|string
     * @throws \Exception
     */
    public function getDataDisplay()
    {
        $cookie = $this->_helperCookie->get(Cookie::COOKIE_NAME);
        if ($cookie != null) {
            $this->_helperCookie->delete();
        }
        /** @var \Magenest\Popup\Model\Popup $popup */
        $popupTemplate = $this->_coreRegistry->registry('popup_template');
        $html_content = $popupTemplate->getHtmlContent();
        $content = $this->_filterProvider->getBlockFilter()->filter($html_content);
        $content .= '<span id="copyright"></span>';
        $content = "<div class='magenest-popup-inner'>" . $content . "</div>";
        $popupTemplate->setHtmlContent($content);

        $data = $popupTemplate->getData();
        $data['preview'] = true;
        return json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }
}
