<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:23 AM
 */

namespace Eguana\ChangeStatus\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const ACTIVE_CHECK_XML_PATH = 'change_status/general/active';

    const AVAILABLE_RETURN_DAYS_XML_PATH = 'change_status/date/available_return_days';

    const RMA_AUTO_AUTHORIZATION_XML_PATH = 'change_status/date/rma_auto_authorization_days';

    const CHANGE_ORDER_STATUS_ACTIVE = 'change_status/change_order_status_cron/active';

    const CHANGE_DELIVERY_COMPLETE_STATUS_ACTIVE = 'change_status/order_status_delivery_complete_cron/active';

    const UPDATE_NINJAVAN_ORDER_TO_DELIVERY_COMPLETE_AFTER_DAYS = 'change_status/date/update_ninjavan_order_to_delivery_complete_days';

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

    public function getValue($path, $type, $storeId)
    {
        return $this->scopeConfig->getValue($path, $type, $storeId);
    }

    public function getCustomRmaActive($storeId)
    {
        return $this->scopeConfig->getValue(self::ACTIVE_CHECK_XML_PATH, 'store', $storeId);
    }

    public function getAvailableReturnDays($storeId)
    {
        return $this->scopeConfig->getValue(self::AVAILABLE_RETURN_DAYS_XML_PATH, 'store', $storeId);
    }

    public function getRmaAutoAuthorizationDays($storeId)
    {
        return $this->scopeConfig->getValue(self::RMA_AUTO_AUTHORIZATION_XML_PATH, 'store', $storeId);
    }

    public function getChangeOrderStatusActive($storeId)
    {
        return $this->scopeConfig->getValue(self::CHANGE_ORDER_STATUS_ACTIVE, 'store', $storeId);
    }

    /**
     * Get Change Order To Delivery Complete Active Value
     *
     * @param null $storeId
     * @return mixed
     */
    public function getChangeOrderToDeliveryCompleteActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::CHANGE_DELIVERY_COMPLETE_STATUS_ACTIVE,
            'store',
            $storeId
        );
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getDaysUpdateNinjaVanOrderToDeliveryComplete($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::UPDATE_NINJAVAN_ORDER_TO_DELIVERY_COMPLETE_AFTER_DAYS,
            'store',
            $storeId
        );
    }
}
