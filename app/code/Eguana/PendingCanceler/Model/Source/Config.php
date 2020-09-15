<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 5:02
 */

namespace Eguana\PendingCanceler\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const PENDING_CANCEL_ACTIVE_CHECK_XML_PATH = 'pending_canceler/general/active';

    const PENDING_ORDERS_CANCEL_TIME_XML_PATH = 'pending_canceler/time/minutes_after_to_cancel';

    /**
     * @var ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getActive($storeId)
    {
        return $this->scopeConfig->getValue(self::PENDING_CANCEL_ACTIVE_CHECK_XML_PATH, 'store', $storeId);
    }

    public function getMinutesToCancel($storeId)
    {
        return $this->scopeConfig->getValue(self::PENDING_ORDERS_CANCEL_TIME_XML_PATH, 'store', $storeId);
    }
}
