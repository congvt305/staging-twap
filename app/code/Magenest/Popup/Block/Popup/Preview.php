<?php
namespace Magenest\Popup\Block\Popup;

class Preview extends \Magento\Framework\View\Element\Template
{
    /** @var  \Magento\Framework\Registry $_coreRegistry */
    protected $_coreRegistry;

    /** @var  \Magenest\Popup\Model\TemplateFactory $_templateFactory */
    protected $_templateFactory;

    /** @var  \Magenest\Popup\Helper\Cookie $_helperCookie */
    protected $_helperCookie;

    /** @var \Magento\Cms\Model\Template\FilterProvider $_filterProvider */
    protected $_filterProvider;

    /**
     * @var \Magento\Framework\Serialize\Serializer\Json
     */
    protected $_json;

    /**
     * Preview constructor.
     * @param \Magento\Cms\Model\Template\FilterProvider $filterProvider
     * @param \Magenest\Popup\Model\TemplateFactory $templateFactory
     * @param \Magenest\Popup\Helper\Cookie $helperCookie
     * @param \Magento\Framework\Registry $coreRegistry
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Framework\Serialize\Serializer\Json $json
     * @param array $data
     */
    public function __construct(
        \Magento\Cms\Model\Template\FilterProvider $filterProvider,
        \Magenest\Popup\Model\TemplateFactory $templateFactory,
        \Magenest\Popup\Helper\Cookie $helperCookie,
        \Magento\Framework\Registry $coreRegistry,
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Framework\Serialize\Serializer\Json $json,
        array $data = []
    ) {
        $this->_filterProvider = $filterProvider;
        $this->_templateFactory = $templateFactory;
        $this->_helperCookie = $helperCookie;
        $this->_coreRegistry = $coreRegistry;
        $this->_json = $json;
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
        $cookie = $this->_helperCookie->get(\Magenest\Popup\Helper\Cookie::COOKIE_NAME);
        if ($cookie != null) {
            $this->_helperCookie->delete();
        }
        /** @var \Magenest\Popup\Model\Popup $popup */
        $popup = $this->_coreRegistry->registry('popup');
        $background_image = $this->_coreRegistry->registry('background_image');
        $html_content = $this->_coreRegistry->registry('html_content')
            ?? $this->getTemplateHtmlContent($this->_coreRegistry->registry('template_id'))
            ?? $popup->getHtmlContent() ?? $this->getTemplateHtmlContent($popup->getPopupTemplateId());
        $content = $this->_filterProvider->getBlockFilter()->filter($html_content);
        $content .= '<span id="copyright"></span>';
        $content = "<div class='magenest-popup-inner'>" . $content . "</div>";
        $popup->setHtmlContent($content);

        $data = $popup->getData();
        $class = $this->getTemplateClassDefault($this->_coreRegistry->registry('template_id')
                ?? $popup->getPopupTemplateId());
        $data['class'] = $class;
        $data['preview'] = true;
        if ($background_image != '0' && (isset($data['background_image']) || $background_image)) {
            if (!$background_image) {
                $imageArr= (array) $this->_json->unserialize($data['background_image']);
                $image = (array) reset($imageArr);
                $background_image = $image['url'];
            }
            $styleExtend = '.magenest-popup-inner{background-image: url(' . $background_image . ') !important;}';
            $data['css_style'] .= $styleExtend;
        }
        return json_encode($data, JSON_HEX_QUOT | JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_NUMERIC_CHECK | JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES | JSON_FORCE_OBJECT | JSON_PRESERVE_ZERO_FRACTION | JSON_UNESCAPED_UNICODE | JSON_PARTIAL_OUTPUT_ON_ERROR);
    }

    /**
     * Get Template Class Default
     *
     * @param string $templateId
     * @return array|mixed|string|null
     */
    public function getTemplateClassDefault($templateId)
    {
        /** @var \Magenest\Popup\Model\Template $templateModel */
        $templateModel = $this->_templateFactory->create()->load($templateId);
        if ($templateModel->getTemplateId()) {
            return $templateModel->getData('class');
        } else {
            return 'popup-default-1';
        }
    }

    /**
     * Get Template HTML Content
     *
     * @param string $templateId
     * @return string|null
     */
    public function getTemplateHtmlContent($templateId)
    {
        /** @var \Magenest\Popup\Model\Template $templateModel */
        $templateModel = $this->_templateFactory->create()->load($templateId);
        return $templateId ? $templateModel->getHtmlContent() : null;
    }
}
