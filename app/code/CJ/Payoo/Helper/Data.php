<?php
declare(strict_types=1);

namespace CJ\Payoo\Helper;

use Magento\Store\Model\ScopeInterface;

/**
 * Class Data
 */
class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    const STATUS_PAYMENT_SUCCESS_PATH_XML = 'payment/paynow/order_payment_status';

    /**
     * @param string $code
     * @param int|null $storeId
     * @return string
     */
    protected function getConfig(string $code, int $storeId = null): string
    {
        return $this->scopeConfig->getValue($code, ScopeInterface::SCOPE_STORE, $storeId);
    }

    /**
     * @param int|null $storeId
     * @return string
     */
    public function getPaymentSuccessStatus(int $storeId = null): string
    {
        return $this->getConfig(self::STATUS_PAYMENT_SUCCESS_PATH_XML, $storeId);
    }
}
