<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/25/20
 * Time: 5:19 PM
 */
declare(strict_types=1);

namespace Eguana\BlackCat\Model\Ui;


use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'blackcat';
    /**
     * @var \Eguana\BlackCat\Helper\Data
     */
    private $helper;

    public function __construct(\Eguana\BlackCat\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function getConfig()
    {
        $isActive = $this->helper->isActive();
        if (!$isActive) {
            [];
        }
        return [
            self::CODE => [
                'isActive' => $isActive,
                'shipping_message' => $this->helper->getCarrierShippingMessage(),
            ],
        ];
    }

}