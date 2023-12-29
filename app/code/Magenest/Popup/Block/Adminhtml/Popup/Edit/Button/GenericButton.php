<?php
namespace Magenest\Popup\Block\Adminhtml\Popup\Edit\Button;

class GenericButton
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $urlBuilder;

    /**
     * @var \Magento\Framework\Registry
     */
    protected $registry;

    /**
     * GenericButton constructor.
     * @param \Magento\Backend\Block\Widget\Context $context
     * @param \Magento\Framework\Registry $registry
     */
    public function __construct(
        \Magento\Backend\Block\Widget\Context $context,
        \Magento\Framework\Registry $registry
    ) {
        $this->urlBuilder = $context->getUrlBuilder();
        $this->registry = $registry;
    }

    /**
     * Get URL
     *
     * @param string $route
     * @param array $params
     * @return string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->urlBuilder->getUrl($route, $params);
    }

    /**
     * Get Popup ID
     *
     * @return int|null
     */
    public function getPopupId()
    {
        $popupModel = $this->registry->registry('popup');
        return $popupModel ? $popupModel->getPopupId() : null;
    }

    /**
     * Get Popup Template ID
     *
     * @return int|null
     */
    public function getPopupTemplateId()
    {
        $templateModel = $this->registry->registry('popup_template');
        return $templateModel ? $templateModel->getTemplateId() : null;
    }

    /**
     * Get Popup
     *
     * @return mixed|null
     */
    public function getPopup()
    {
        return $this->registry->registry('popup');
    }
}
