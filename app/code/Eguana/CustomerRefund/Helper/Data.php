<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/5/20
 * Time: 8:08 AM
 */

namespace Eguana\CustomerRefund\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;

class Data extends AbstractHelper
{
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;

    public function __construct(
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        Context $context
    )
    {
        parent::__construct($context);
        $this->encryptor = $encryptor;
    }

    const XML_PATH_ENABLED = 'eguana_cutomerrefund/general/enabled';
    const XML_PATH_BANKINFO_ENCRYPTION_KEY = 'eguana_cutomerrefund/bankinfo/encryption_key';

    public function isEnabled() {
        return $this->scopeConfig->getValue(
            $this::XML_PATH_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
    }

    public function getEncryptionKey() {
        $encrypedKey =  $this->scopeConfig->getValue(
            $this::XML_PATH_BANKINFO_ENCRYPTION_KEY,
            \Magento\Store\Model\ScopeInterface::SCOPE_WEBSITE
        );
        return $this->encryptor->decrypt($encrypedKey);
    }

}
