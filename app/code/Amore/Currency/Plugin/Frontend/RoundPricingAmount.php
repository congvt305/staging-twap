<?php
declare(strict_types=1);

namespace Amore\Currency\Plugin\Frontend;

use Amore\Currency\Model\PriceCurrency;
use Magento\Framework\Pricing\Render\Amount;

class RoundPricingAmount
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
     * @see \Magento\Framework\Pricing\Render\Amount::getDisplayValue
     * @param Amount $subject,
     * @param float $value
     */
    public function afterGetDisplayValue(
        Amount $subject,
        $value
    ) {
        if ($this->priceCurrency->isEnabled()) {
            return $this->priceCurrency->round($value);
        }
        return $value;
    }
}
