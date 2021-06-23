<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2020/07/20
 * Time: 5:42 PM
 */

namespace Eguana\EInvoice\Model\Source;

use Magento\Framework\App\Config\ScopeConfigInterface;

class Config
{
    const ACTIVE_CHECK_XML_PATH = 'eguana_einvoice/ecpay_einvoice_issue/active';
    const SENDER_NAME_XML_PATH = "eguana_einvoice/ecpay_einvoice_issue/sender_name";
    const SENDER_EMAIL_XML_PATH = "eguana_einvoice/ecpay_einvoice_issue/sender_email";
    const RECEIVER_EMAIL_XML_PATH = "eguana_einvoice/ecpay_einvoice_issue/receiver_email";
    const DAYS_LIMIT_XML_PATH = "eguana_einvoice/days_limit_when_get_orders/set_days";

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * Config constructor.
     * @param ScopeConfigInterface $scopeConfig
     */
    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    /**
     * @param $path
     * @param $type
     * @param $storeId
     * @return mixed
     */
    public function getValue($path, $type, $storeId)
    {
        return $this->scopeConfig->getValue($path, $type, $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getEInvoiceIssueActive($storeId)
    {
        return $this->getValue(self::ACTIVE_CHECK_XML_PATH, 'store', $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getSenderName($storeId)
    {
        return $this->getValue(self::SENDER_NAME_XML_PATH, "store", $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getSenderEmail($storeId)
    {
        return $this->getValue(self::SENDER_EMAIL_XML_PATH, "store", $storeId);
    }

    /**
     * @param $storeId
     * @return mixed
     */
    public function getReceiverEmail($storeId)
    {
        return $this->getValue(self::RECEIVER_EMAIL_XML_PATH, "store", $storeId);
    }

    /**
     * This method is used to get the completed order days limit
     * @param $storeId
     * @return mixed
     */
    public function getDaysLimit($storeId)
    {
        return $this->getValue(self::DAYS_LIMIT_XML_PATH, "store", $storeId);
    }
}
