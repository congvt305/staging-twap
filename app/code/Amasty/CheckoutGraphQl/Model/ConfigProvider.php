<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model;

use Amasty\Base\Model\ConfigProviderAbstract;
use Magento\GiftMessage\Helper\Message as GiftMessageHelper;
use Magento\Store\Model\ScopeInterface;

class ConfigProvider extends ConfigProviderAbstract
{
    /**
     * xpath prefix of module (section)
     *
     * @var string
     */
    protected $pathPrefix = self::PATH_PREFIX;

    /**
     * Path Prefix For Config
     */
    public const PATH_PREFIX = 'amasty_checkout/';

    public function isAllowOrderGiftMessage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ORDER,
            ScopeInterface::SCOPE_STORE
        );
    }

    public function isAllowOrderItemsGiftMessage(): bool
    {
        return $this->scopeConfig->isSetFlag(
            GiftMessageHelper::XPATH_CONFIG_GIFT_MESSAGE_ALLOW_ITEMS,
            ScopeInterface::SCOPE_STORE
        );
    }
}
