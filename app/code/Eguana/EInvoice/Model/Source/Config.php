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
    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    public function __construct(
        ScopeConfigInterface $scopeConfig
    ) {
        $this->scopeConfig = $scopeConfig;
    }

    public function getValue($path, $type, $storeId)
    {
        return $this->scopeConfig->getValue($path, $type, $storeId);
    }

    public function getEInvoiceIssueActive($storeId)
    {
        return $this->getValue(self::ACTIVE_CHECK_XML_PATH, 'store', $storeId);
    }
}
