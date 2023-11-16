<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Block\Adminhtml\System\Config;

use Magento\Backend\Block\Template\Context;
use Magento\Config\Block\System\Config\Form\Field;
use Magento\Framework\Data\Form\Element\AbstractElement;
use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Date extends Field
{
    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        TimezoneInterface $timezone,
        Context $context,
        array $data = []
    ) {
        parent::__construct($context, $data);
        $this->timezone = $timezone;
    }

    /**
     * @param AbstractElement $element
     * @return string
     */
    public function render(AbstractElement $element)
    {
        $element->setDateFormat(DateTime::DATE_INTERNAL_FORMAT);
        $element->setTimeFormat(null);
        $element->setMinDate($this->timezone->scopeDate()->format(DateTime::DATE_PHP_FORMAT));
        return parent::render($element);
    }
}
