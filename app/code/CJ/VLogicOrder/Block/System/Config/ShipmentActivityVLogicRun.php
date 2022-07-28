<?php

namespace CJ\VLogicOrder\Block\System\Config;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

class ShipmentActivityVLogicRun extends Field
{
    protected $_template = 'CJ_VLogicOrder::system/config/shipment_activity_run.phtml';

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
        return $this->getUrl('vlogic/system_config/shipmentactivityrun');
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
                    'id' => 'run_cron',
                    'label' => __('Run Now'),
                ]
            );
        } catch (LocalizedException $exception) {
            $exception->getMessage();
        }

        return $button->toHtml();
    }
}
