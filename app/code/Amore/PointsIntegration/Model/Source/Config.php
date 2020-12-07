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

    const POS_ORDER_ACTIVE_CHECK = 'points_integration/pos_order_cron/active';

    const SSL_VERIFICATION_CHECK = 'points_integration/general/ssl_verification';

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

    public function getPosUrl($websiteId)
    {
        return $this->getValue(self::POS_URL_PATH, ScopeInterface::SCOPE_WEBSITE, $websiteId);
    }

    public function getSSLVerification()
    {
        return $this->scopeConfig->getValue(self::SSL_VERIFICATION_CHECK);
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
}
