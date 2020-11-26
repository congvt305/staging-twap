<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/19/20
 * Time: 12:50 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Config\Frontend;

class CreateShipments extends \Magento\Config\Block\System\Config\Form\Field
{
    /**
     * @var string
     */
    public $buttonLabel = 'Run Now';

    /**
     * @param string $buttonLabel
     *
     * @return $this
     */
    public function setButtonLabel($buttonLabel)
    {
        $this->buttonLabel = $buttonLabel;

        return $this;
    }

    /**
     * Get the button and scripts contents.
     *
     * @param \Magento\Framework\Data\Form\Element\AbstractElement $element
     *
     * @return string
     * @SuppressWarnings(PHPMD.UnusedFormalParameter)
     */
    public function _getElementHtml(\Magento\Framework\Data\Form\Element\AbstractElement $element)
    {
        $url = $this->_urlBuilder->getUrl('eguana_gwlogistics/run/createshipments');

        return $this->getLayout()
            ->createBlock(\Magento\Backend\Block\Widget\Button::class)
            ->setType('button')
            ->setLabel($this->buttonLabel)
            ->setOnClick("window.location.href='" . $url . "'")
            ->toHtml();
    }
}