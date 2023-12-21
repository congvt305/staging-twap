<?php
declare(strict_types=1);

namespace Amore\Currency\Observer\Frontend;

use Amore\Currency\Model\PriceCurrency;
use Magento\Framework\Event\Observer;
use Magento\Framework\Event\ObserverInterface;
use Magento\Framework\Locale\FormatInterface;

class OptionPriceFormat implements ObserverInterface
{
    /**
     * @var PriceCurrency
     */
    private $priceCurrency;

    /**
     * @var FormatInterface
     */
    private $format;

    /**
     * RoundFinalPrice constructor.
     * @param PriceCurrency $priceCurrency
     * @param FormatInterface $format
     */
    public function __construct(
        PriceCurrency $priceCurrency,
        FormatInterface $format
    ) {
        $this->priceCurrency = $priceCurrency;
        $this->format = $format;
    }

    /**
     * @see \Magento\Catalog\Block\Product\View\Options::getJsonConfig
     * @see vendor/magento/module-catalog/view/base/web/js/price-options.js::_applyOptionNodeFix
     * @param Observer $observer
     * @return void
     */
    public function execute(Observer $observer)
    {
        if ($this->priceCurrency->isEnabled()) {
            $configObj = $observer->getData('configObj');
            $config = $configObj->getData('config') ?: [];
            $config['priceFormat'] = $this->format->getPriceFormat();
            $configObj->setData('config', $config);
        }
    }
}
