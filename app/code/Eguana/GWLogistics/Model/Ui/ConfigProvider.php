<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/25/20
 * Time: 4:56 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gwlogistics';
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    public function __construct(\Eguana\GWLogistics\Helper\Data $helper)
    {
        $this->helper = $helper;
    }

    public function getConfig()
    {
        $isActive = $this->helper->isActive();
        if (!$isActive) {
            return;
        }
        return [
                self::CODE => [
                    'isActive' => $isActive,
                    'shipping_message' => $this->helper->getCarrierShippingMessage(),
                ],
        ];
    }
}
