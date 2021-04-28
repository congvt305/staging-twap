<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/4/21
 * Time: 4:00 PM
 */
namespace Eguana\ChangeStatus\Block\System\Config;

use Magento\Backend\Block\Widget\Button;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Exception\LocalizedException;

/**
 * Class DeliveryCompleteCronRun
 *
 * Block class to show cron button in configuration
 */
class DeliveryCompleteCronRun extends Field
{
    /**#@+
     * Constants for cron
     */
    const CRON_RUN_PATH = 'change_status/system_config/runDeliveryCompleteCron';
    /**#@-*/

    /**
     * @var string
     */
    protected $_template = 'Eguana_ChangeStatus::system/config/delivery_complete_run.phtml';

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
        return $this->getUrl(self::CRON_RUN_PATH);
    }

    /**
     * Generate synchronize button html
     *
     * @return mixed
     */
    public function getButtonHtml()
    {
        try {
            $button = $this->getLayout()->createBlock(
                Button::class
            )->setData(
                [
                    'id' => 'delivery_complete_cron',
                    'label' => __('Run Now'),
                ]
            );
        } catch (LocalizedException $exception) {
            $this->_logger->error($exception->getMessage());
        }

        return $button->toHtml();
    }
}
