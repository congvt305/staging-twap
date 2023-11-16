<?php
declare(strict_types=1);

namespace Amore\Currency\Model;

use Magento\Framework\App\Config\ScopeConfigInterface;

class PriceCurrency
{
    const ROUNDUP_CONFIG = 'sales/amore_order/enabled_roundup_price';

    const AP_DEFAULT_PRECISION = 0;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * PriceCurrency constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @return bool
     */
    public function isEnabled()
    {
        return $this->scopeConfig->isSetFlag(self::ROUNDUP_CONFIG);
    }

    /**
     * @param $price
     * @return float
     */
    public function round($price)
    {
        return round((float) $price, self::AP_DEFAULT_PRECISION);
    }
}
