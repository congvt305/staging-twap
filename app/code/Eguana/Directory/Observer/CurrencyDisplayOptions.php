<?php

namespace Eguana\Directory\Observer;

use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;

class CurrencyDisplayOptions implements ObserverInterface
{
    public function execute(Observer $observer)
    {
        $baseCode = $observer->getEvent()->getBaseCode();
        if ($baseCode == "TWD") {
            $currencyOptions = $observer->getEvent()->getCurrencyOptions();
            $currencyOptions->setData('format', '¤#,##0');
            $currencyOptions->setData('percision', 0);
        }
        return $this;
    }
}
