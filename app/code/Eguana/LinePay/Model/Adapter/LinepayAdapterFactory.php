<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 6/10/20
 * Time: 3:21 PM
 */
namespace Eguana\LinePay\Model\Adapter;

use Eguana\LinePay\Gateway\Config\Config;
use Magento\Framework\ObjectManagerInterface;

/**
 * This factory is preferable to use for LINE Pay adapter instance creation.
 */
class LinepayAdapterFactory
{
    /**
     * @var ObjectManagerInterface
     */
    private $objectManager;

    /**
     * @var Config
     */
    private $config;

    /**
     * @param ObjectManagerInterface $objectManager
     * @param Config $config
     */
    public function __construct(ObjectManagerInterface $objectManager, Config $config)
    {
        $this->config = $config;
        $this->objectManager = $objectManager;
    }

    /**
     * Create credentials and place order
     * @param null $storeId
     * @return mixed
     */
    public function create($storeId = null)
    {
        return $this->objectManager->create(
            LinepayAdapter::class,
            [
                'merchantId' => $this->config->getValue(Config::KEY_MERCHANT_ID, $storeId),
                'privateKey' => $this->config->getValue(Config::KEY_PRIVATE_KEY, $storeId),
                'environment' => $this->config->getEnvironment($storeId),
            ]
        );
    }
}
