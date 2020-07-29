<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:16 AM
 */

namespace Eguana\ChangeStatus\Block\System\Config;

use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Backend\Block\Widget\Button;
use Magento\Framework\Exception\LocalizedException;

/**
 * This class is responsible for run button in admin
 * Class Run
 */
class RmaAuthorizedRun extends Field
{
    protected $_template = 'Eguana_ChangeStatus::system/config/rma_authorized_run.phtml';

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
        return $this->getUrl('change_status/system_config/rmacheck');
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
