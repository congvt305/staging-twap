<?php
declare(strict_types=1);

namespace Amore\Currency\Plugin;

use Amore\Currency\Model\PriceCurrency;
use Magento\Catalog\Model\Product\Option\Type\DefaultType;

class RoundCustomOptionPrice
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
     * @see \Magento\Catalog\Model\Product\Option\Type\DefaultType::getOptionPrice
     * @param DefaultType $subject
     * @param float $result
     * @return float
     */
    public function afterGetOptionPrice(
        DefaultType $subject,
        $result
    ) {
        if ($this->priceCurrency->isEnabled()) {
            return $this->priceCurrency->round($result);
        }
        return $result;
    }
}
