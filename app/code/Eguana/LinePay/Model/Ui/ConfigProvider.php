<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 7/9/20
 * Time: 2:28 PM
 */
namespace Eguana\LinePay\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Payment\Helper\Data as PaymentHelper;

/**
 * Class ConfigProvider logo src
 */
class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'linepay_payment';

    /**
     * @var \Eguana\LinePay\Helper\Data
     */
    private $linePayHelper;

    /**
     * ConfigProvider constructor.
     * @param \Eguana\LinePay\Helper\Data $linePayHelper
     */
    public function __construct(
        \Eguana\LinePay\Helper\Data $linePayHelper
    ) {
        $this->linePayHelper = $linePayHelper;
    }

    /**
     * Retrieve assoc array of checkout configuration
     *
     * @return array
     */
    public function getConfig()
    {
        return [
            'payment' => [
                self::CODE => [
                    'linePayLogoSrc'  => $this->linePayHelper->getLinePayLogoSrc()
                ]
            ]
        ];
    }
}
