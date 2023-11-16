<?php
declare(strict_types=1);

namespace Amore\Currency\Plugin\Frontend;

use Amore\Currency\Model\PriceCurrency;
use Magento\Framework\Locale\FormatInterface;

class RemoveDecimal
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
     * Remove decimal point from price format of window.checkoutConfig and json config of product display
     * @see FormatInterface::getPriceFormat()
     * @param FormatInterface $subject
     * @param $result
     */
    public function afterGetPriceFormat(
        FormatInterface $subject,
        $result
    ) {
        if ($this->priceCurrency->isEnabled()) {
            return array_replace(
                $result,
                [
                    'precision' => 0,
                    'requiredPrecision' => 0,
                    'integerRequired' => true
                ]
            );
        }
        return $result;
    }
}
