<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Model\Config;
use Amasty\CheckoutCore\Model\ConfigProvider as CheckoutConfigProvider;
use Amasty\CheckoutDeliveryDate\Model\ConfigProvider as DDConfigProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Escaper;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Module\Manager;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class CheckoutConfiguration implements ResolverInterface
{
    public const DD_MODULE = 'Amasty_CheckoutDeliveryDate';

    /**
     * @var Escaper
     */
    private $escaper;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var Config
     */
    private $configProvider;

    /**
     * @var CheckoutConfigProvider
     */
    private $checkoutConfig;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Escaper $escaper,
        Json $jsonSerializer,
        Config $configProvider,
        CheckoutConfigProvider $checkoutConfig,
        Manager $moduleManager,
        TimezoneInterface $timezone
    ) {
        $this->escaper = $escaper;
        $this->jsonSerializer = $jsonSerializer;
        $this->configProvider = $configProvider;
        $this->checkoutConfig = $checkoutConfig;
        $this->moduleManager = $moduleManager;
        $this->timezone = $timezone;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return [
            'amasty_checkout_design_font' => $this->getCustomFont(),
            'amasty_checkout_delivery_date_format' => $this->getDateFormat(),
            'amasty_checkout_delivery_date_available_days' => $this->getAvailableDeliveryDays(),
            'amasty_checkout_delivery_date_available_hours' => $this->getAvailableDeliveryHours(),
            'amasty_checkout_layout_builder_frontend_layout_config' => $this->getLayoutBlocksConfigs()
        ];
    }

    /**
     * @return string
     */
    private function getCustomFont(): string
    {
        $font = (string)$this->configProvider->getCustomFont();

        return (string)$this->escaper->escapeHtml(strtok(trim($font), ':'));
    }

    /**
     * @return string
     */
    private function getDateFormat(): string
    {
        return $this->timezone->getDateFormat();
    }

    /**
     * @return string
     */
    private function getAvailableDeliveryDays(): string
    {
        $days = [];
        if ($this->moduleManager->isEnabled(self::DD_MODULE)) {
            $ddConfigProvider = ObjectManager::getInstance()->get(DDConfigProvider::class);
            $days = $ddConfigProvider->getDeliveryDays();
        }

        return $this->jsonSerializer->serialize($days);
    }

    /**
     * @return string
     */
    private function getAvailableDeliveryHours(): string
    {
        $hours = [];
        if ($this->moduleManager->isEnabled(self::DD_MODULE)) {
            $ddConfigProvider = ObjectManager::getInstance()->get(DDConfigProvider::class);
            $hours = $ddConfigProvider->getDeliveryHours();
        }

        return $this->jsonSerializer->serialize($hours);
    }

    /**
     * @return string
     */
    private function getLayoutBlocksConfigs(): string
    {
        return $this->jsonSerializer->serialize($this->checkoutConfig->getCheckoutBlocksConfig());
    }
}
