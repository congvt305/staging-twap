<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 12:11
 */

namespace Amore\PointsIntegration\Block\System\Points\Config;

use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class SendOrderToPos
{
    protected $_template = 'Amore_PointsIntegration::system/points/config/order_to_pos.phtml';
    /**
     * @var \Magento\Framework\App\State
     */
    private $state;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Magento\Backend\Model\Auth\Session
     */
    private $authSession;

    /**
     * SendOrderToPos constructor.
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Backend\Model\Auth\Session $authSession
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Backend\Model\Auth\Session $authSession,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->storeManager = $storeManager;
        $this->authSession = $authSession;
    }

    /**
     * Remove scope label
     *
     * @param  AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->unsScope()->unsCanUseWebsiteValue()->unsCanUseDefaultValue();
        return parent::render($element);
    }

    /**
     * Return element html
     *
     * @param  AbstractElement $element
     * @return string
     *
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    protected function _getElementHtml(AbstractElement $element)
    {
        parent::_getElementHtml($element);
        return $this->_toHtml();
    }

    /**
     * Return ajax url for synchronize button
     *
     * @return string
     */
    public function getAjaxUrl()
    {
        return $this->getUrl('Amore_PointsIntegration/backend_system_points_config/sendordertopos');
    }

    /**
     * Generate synchronize button html
     *
     * @return mixed
     * @throws LocalizedException
     */
    public function getButtonHtml()
    {
        try {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'id' => 'order_to_pos',
                    'label' => __('Run Now'),
                ]
            );
        } catch (LocalizedException $exception) {
            $exception->getMessage();
        }

        return $button->toHtml();
    }

    /**
     * @return int
     */
    public function resolveCurrentWebsiteId()
    {
        /** @var \Magento\Framework\App\RequestInterface $request */
        $request = $this->_request;
        $params = $request->getParams();

        if (key_exists('website', $params)) {
            return $request->getParam('website');
        } else {
            return 0;
        }
    }
}
