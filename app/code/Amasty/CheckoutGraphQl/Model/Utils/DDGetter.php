<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Utils;

use Amasty\CheckoutDeliveryDate\Api\Data\DeliveryInterface;
use Amasty\CheckoutDeliveryDate\Model\DeliveryDateProvider;
use Magento\Framework\App\ObjectManager;

class DDGetter
{
    public const DD_MODULE = 'Amasty_CheckoutDeliveryDate';

    public const DATE_KEY = 'date';
    public const TIME_KEY = 'time';
    public const COMMENT_KEY = 'comment';

    public function getByQuoteId(int $quoteId): DeliveryInterface
    {
        return ObjectManager::getInstance()->get(DeliveryDateProvider::class)->findByQuoteId($quoteId);
    }

    public function getByOrderId(int $orderId): DeliveryInterface
    {
        return ObjectManager::getInstance()->get(DeliveryDateProvider::class)->findByOrderId($orderId);
    }
}
