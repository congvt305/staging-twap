<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 14/10/20
 * Time: 4:45 PM
 */
namespace Eguana\LinePay\Gateway\Config;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Serialize\Serializer\Json;

/**
 * Class Config
 *
 * Payment Config
 */
class Config extends \Magento\Payment\Gateway\Config\Config
{

    const KEY_ENVIRONMENT = 'environment';
    const KEY_MERCHANT_ID = 'merchant_id';
    const KEY_PRIVATE_KEY = 'private_key';

    /**
     * @var
     */
    private $serializer;

    /**
     * Get environment
     * @param null $storeId
     * @return mixed|null
     */
    public function getEnvironment($storeId = null)
    {
        return $this->getValue(Config::KEY_ENVIRONMENT, $storeId);
    }

    /**
     * Get merchant id
     * @param null $storeId
     * @return mixed|null
     */
    public function getMerchantId($storeId = null)
    {
        return $this->getValue(Config::KEY_MERCHANT_ID, $storeId);
    }

    /**
     * Get private key
     * @param null $storeId
     * @return mixed|null
     */
    public function getPrivateKey($storeId = null)
    {
        return $this->getValue(self::KEY_PRIVATE_KEY, $storeId);
    }
}
