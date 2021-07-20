<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 11:13
 */

namespace Amore\PointsIntegration\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Store\Model\ScopeInterface;

class Config
{
    const SALES_ORGANIZATION_CODE = 'points_integration/pos/sales_organization_code';

    const SALES_OFFICE_CODE = 'points_integration/pos/sales_office_code';

    const POS_URL_PATH = 'points_integration/pos/url';

    const POS_MEMBER_SEARCH_URL = 'points_integration/pos/member_search';

    const POS_REDEEM_SEARCH_URL = 'points_integration/pos/redeem_search';

    const POS_POINT_SEARCH_URL = 'points_integration/pos/point_search';

    const POS_CUSTOMER_ORDER_URL = 'points_integration/pos/customer_order';

    const POS_ORDER_ACTIVE_CHECK = 'points_integration/general/pos_order_active';

    const POS_RMA_ACTIVE_CHECK = 'points_integration/general/pos_rma_active';

    const SSL_VERIFICATION_CHECK = 'points_integration/general/ssl_verification';

    const POINTS_INTEGRATION_ACTIVE_CHECK = 'points_integration/general/active';

    const LOGGER_ACTIVE_CHECK = 'points_integration/general/logging';

    const SEND_ORDER_TO_POS_CRON_ACTIVE = 'points_integration/configurable_cron/active';

    const AVAILABLE_DAYS_AFTER_ORDER_COMPLETE = 'points_integration/configurable_cron/available_send_days';

    const CRON_TEST_ACTIVE_CHECK = 'points_integration/configurable_cron/test_active';

    const CRON_TEST_ORDER_INCREMENT_ID_GTEQ = 'points_integration/configurable_cron/test_order_no_gteq';

    const CRON_TEST_ORDER_INCREMENT_ID_LTEQ = 'points_integration/configurable_cron/test_order_no_lteq';

    const REWARD_BLOCK_ID_PATH = 'points_integration/general/rewards_block_id';

    const REDEMPTION_BLOCK_ID_PATH = 'points_integration/general/redemption_block_id';

    const POINTS_BLOCK_ID_PATH = 'points_integration/general/points_block_id';

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

    public function getActive($websiteId)
    {
        return $this->getValue(self::POINTS_INTEGRATION_ACTIVE_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getPosOrderActive($websiteId)
    {
        return $this->getValue(self::POS_ORDER_ACTIVE_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getPosRmaActive($websiteId)
    {
        return $this->getValue(self::POS_RMA_ACTIVE_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getLoggerActiveCheck($websiteId)
    {
        return $this->getValue(self::LOGGER_ACTIVE_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getPosUrl($websiteId)
    {
        return $this->getValue(self::POS_URL_PATH, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getSSLVerification($websiteId)
    {
        return $this->scopeConfig->getValue(self::SSL_VERIFICATION_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getOrganizationSalesCode($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::SALES_ORGANIZATION_CODE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::SALES_ORGANIZATION_CODE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getOfficeSalesCode($websiteId = null)
    {
        if ($websiteId) {
            return $this->scopeConfig->getValue(
                self::SALES_OFFICE_CODE,
                ScopeInterface::SCOPE_WEBSITE,
                $websiteId
            );
        }

        return $this->scopeConfig->getValue(
            self::SALES_OFFICE_CODE,
            ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getMemberSearchURL($websiteId)
    {
        $baseURL = $this->getPosUrl($websiteId);
        $memberSearchURL = $this->scopeConfig->getValue(
            self::POS_MEMBER_SEARCH_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return $baseURL.$memberSearchURL;
    }

    public function getRedeemSearchURL($websiteId)
    {
        $baseURL = $this->getPosUrl($websiteId);
        $redeemSearchURL = $this->scopeConfig->getValue(
            self::POS_REDEEM_SEARCH_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return $baseURL.$redeemSearchURL;
    }

    public function getPointSearchURL($websiteId)
    {
        $baseURL = $this->getPosUrl($websiteId);
        $pointSearchURL = $this->scopeConfig->getValue(
            self::POS_POINT_SEARCH_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return $baseURL.$pointSearchURL;
    }

    public function getCustomerOrderURL($websiteId)
    {
        $baseURL = $this->getPosUrl($websiteId);
        $customerOrderURL = $this->scopeConfig->getValue(
            self::POS_CUSTOMER_ORDER_URL,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );

        return $baseURL.$customerOrderURL;
    }

    public function getCronActive($websiteId)
    {
        return $this->getValue(self::SEND_ORDER_TO_POS_CRON_ACTIVE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getDaysForCompletedOrder($websiteId)
    {
        return $this->getValue(self::AVAILABLE_DAYS_AFTER_ORDER_COMPLETE, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getCronTestActive($websiteId)
    {
        return $this->getValue(self::CRON_TEST_ACTIVE_CHECK, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getCronTestOrderGteq($websiteId)
    {
        return $this->getValue(self::CRON_TEST_ORDER_INCREMENT_ID_GTEQ, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getCronTestOrderLteq($websiteId)
    {
        return $this->getValue(self::CRON_TEST_ORDER_INCREMENT_ID_LTEQ, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    /**
     * Get config rewards block id
     *
     * @param $websiteId
     * @return mixed
     */
    public function getRewardsBlock($websiteId)
    {
        return $this->getValue(
            self::REWARD_BLOCK_ID_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get config redemption block id
     *
     * @param $websiteId
     * @return mixed
     */
    public function getRedemptionBlock($websiteId)
    {
        return $this->getValue(
            self::REDEMPTION_BLOCK_ID_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }

    /**
     * Get config points block id
     *
     * @param $websiteId
     * @return mixed
     */
    public function getPointsBlock($websiteId)
    {
        return $this->getValue(
            self::POINTS_BLOCK_ID_PATH,
            ScopeInterface::SCOPE_WEBSITE,
            $websiteId
        );
    }
}
