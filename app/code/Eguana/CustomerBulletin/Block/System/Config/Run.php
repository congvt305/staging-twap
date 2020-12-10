<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Shahroz
 * Date: 11/29/19
 * Time: 8:03 PM
 */
namespace Eguana\CustomerBulletin\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for run button in admin
 * Class Run
 */
class Run extends Field
{
    const  CONTROLLER_ROUTE = 'ticket/system_config/run';
    /**
     * @var string
     */
    protected $_template = 'Eguana_CustomerBulletin::system/config/run.phtml';

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
        return $this->getUrl(self::CONTROLLER_ROUTE);
    }

    /**
     * Generate synchronize button html
     *
     * @return mixed
     */
    public function getButtonHtml()
    {
        $button = '';
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
