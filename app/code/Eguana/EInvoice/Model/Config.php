<?php

namespace Eguana\EInvoice\Model;

/**
 * Class Config
 */
class Config
{
    const XML_PATH_MERCHANT_ID = 'payment/ecpay_ecpaypayment/ecpay_merchant_id';
    const XML_PATH_EINVOICE_HASH_KEY = 'payment/ecpay_ecpaypayment/ecpay_invoice/ecpay_invoice_hash_key';
    const XML_PATH_EINVOICE_HASH_IV = 'payment/ecpay_ecpaypayment/ecpay_invoice/ecpay_invoice_hash_iv';
    const XML_PATH_ECPAY_QUERY_TEST_FLAG = 'eguana_einvoice/ecpay_einvoice_issue/ecpay_query_test_flag';
    const XML_PATH_ECPAY_QUERY_STAGE_URL = 'eguana_einvoice/ecpay_einvoice_issue/ecpay_query_stage_url';
    const XML_PATH_ECPAY_QUERY_PRODUCTION_URL = 'eguana_einvoice/ecpay_einvoice_issue/ecpay_query_production_url';

    /**
     * @var \Magento\Framework\App\Config\ScopeConfigInterface
     */
    protected $scopeConfig;

    /**
     * Constructor
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     */
    public function __construct(\Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig)
    {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * Check whether it is on test mode
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @return bool
     */
    protected function getTestFlag(?int $storeId, string $scopeType = 'websites'): bool
    {
        return (bool)$this->scopeConfig->getValue(self::XML_PATH_ECPAY_QUERY_TEST_FLAG, $scopeType, $storeId);
    }

    /**
     * Return staging API Endpoint that queries invoice information
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @param string $suffix
     * @return string
     */
    protected function getStageUrl(?int $storeId, string $scopeType = 'default', string $suffix = 'Issue'): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ECPAY_QUERY_STAGE_URL, $scopeType) . $suffix;
    }

    /**
     * Return production API Endpoint that queries invoice information
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @param string $suffix
     * @return string
     */
    protected function getProdUrl(?int $storeId, string $scopeType = 'default', string $suffix = 'Issue'): string
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ECPAY_QUERY_PRODUCTION_URL, $scopeType) . $suffix;
    }

    /**
     * Return config hash key
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @return string
     */
    public function getHashKey(
        ?int $storeId,
        string $scopeType = 'store'
    ): string {
        return $this->scopeConfig->getValue(self::XML_PATH_EINVOICE_HASH_KEY, $scopeType, $storeId);
    }

    /**
     * Return config hash iv
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @return string
     */
    public function getHashIv(
        ?int $storeId,
        string $scopeType = 'store'
    ): string {
        return $this->scopeConfig->getValue( self::XML_PATH_EINVOICE_HASH_IV, $scopeType, $storeId);
    }

    /**
     * Return config merchant id
     *
     * @param int|null $storeId
     * @param string $scopeType
     * @return string
     */
    public function getMerchantId(
        ?int $storeId,
        string $scopeType = 'store'
    ): string {
        return $this->scopeConfig->getValue( self::XML_PATH_MERCHANT_ID, $scopeType, $storeId);
    }

    /**
     * Get API Query EInvoice endpoint
     *
     * @param int|null $storeId
     * @return string
     */
    public function getQueryEInvoiceApiUrl(?int $storeId): string
    {
        return $this->getTestFlag($storeId) ? $this->getStageUrl($storeId) : $this->getProdUrl($storeId);
    }
}
