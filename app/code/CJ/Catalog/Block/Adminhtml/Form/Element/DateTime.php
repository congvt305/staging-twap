<?php
declare(strict_types=1);

namespace CJ\Catalog\Block\Adminhtml\Form\Element;

use Magento\Framework\Data\Form\Element\CollectionFactory;
use Magento\Framework\Data\Form\Element\Date;
use Magento\Framework\Data\Form\Element\Factory;
use Magento\Framework\Escaper;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateTime  extends Date {

    /**
     * @param Factory $factoryElement
     * @param CollectionFactory $factoryCollection
     * @param Escaper $escaper
     * @param TimezoneInterface $localeDate
     * @param $data
     */
    public function __construct(
        Factory $factoryElement,
        CollectionFactory $factoryCollection,
        Escaper $escaper,
        TimezoneInterface $localeDate,
        $data = []
    ) {
        parent::__construct(
            $factoryElement,
            $factoryCollection,
            $escaper,
            $localeDate,
            $data
        );
    }

    /**
     * @return string
     * @throws \Exception
     */
    public function getElementHtml() {
        $this->setDateFormat($this->localeDate->getDateFormat(\IntlDateFormatter::SHORT));
        $this->setTimeFormat($this->localeDate->getTimeFormat(\IntlDateFormatter::SHORT));
        return parent::getElementHtml();
    }
}
