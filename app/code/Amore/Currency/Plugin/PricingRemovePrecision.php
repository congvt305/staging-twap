<?php
declare(strict_types=1);

namespace Amore\Currency\Plugin;

use Amore\Currency\Model\PriceCurrency;
use Magento\Directory\Model\Currency;

class PricingRemovePrecision
{
    /**
     * @var  PriceCurrency
     */
    private $priceCurrency;

    /**
     * Rounded constructor.
     * @param  PriceCurrency $priceCurrency
     */
    public function __construct(
        PriceCurrency $priceCurrency
    ) {
        $this->priceCurrency = $priceCurrency;
    }


    /**
     * Same ideal with Eguana\Directory plugin
     * @see \Eguana\Directory\Plugin\Model\Currency::beforeFormatPrecision
     * @see Currency::formatPrecision
     * @param Currency $subject
     * @param   float $price
     * @param   int $precision
     * @param   array $options
     * @param   bool $includeContainer
     * @param   bool $addBrackets
     * @return mixed
     */
    public function beforeFormatPrecision(
        Currency $subject,
        $price,
        $precision,
        $options = [],
        $includeContainer = true,
        $addBrackets = false
    ) {
        if ($this->priceCurrency->isEnabled()) {
            return [$price, 0, $options, $includeContainer, $addBrackets];
        }
        return [$price, $precision, $options, $includeContainer, $addBrackets];
    }
}
