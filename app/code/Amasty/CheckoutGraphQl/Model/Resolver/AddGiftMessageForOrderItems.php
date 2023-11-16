<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutGraphQl\Model\ConfigProvider;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Amasty\CheckoutGraphQl\Model\Utils\GiftMessageProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\ItemRepositoryInterface;

class AddGiftMessageForOrderItems implements ResolverInterface
{
    public const CART_ITEMS_KEY = 'cart_items';
    public const ITEM_ID_KEY = 'item_id';

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var ItemRepositoryInterface
     */
    private $itemRepository;

    /**
     * @var GiftMessageProvider
     */
    private $giftMessageProvider;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    public function __construct(
        CartProvider $cartProvider,
        ItemRepositoryInterface $itemRepository,
        GiftMessageProvider $giftMessageProvider,
        ConfigProvider $configProvider
    ) {
        $this->cartProvider = $cartProvider;
        $this->itemRepository = $itemRepository;
        $this->giftMessageProvider = $giftMessageProvider;
        $this->configProvider = $configProvider;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!$this->configProvider->isAllowOrderItemsGiftMessage()) {
            throw new GraphQlInputException(__('Gift message for order items is not allowed.'));
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        if (empty($args['input'][self::CART_ITEMS_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', self::CART_ITEMS_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        foreach ($args['input'][self::CART_ITEMS_KEY] as $itemMessage) {
            if (empty($itemMessage[self::ITEM_ID_KEY])) {
                throw new GraphQlInputException(__('Required parameter "%1" is missing', self::ITEM_ID_KEY));
            }

            try {
                $message = $this->giftMessageProvider->prepareGiftMessage($itemMessage);
                $this->itemRepository->save($cart->getId(), $message, $itemMessage[self::ITEM_ID_KEY]);
            } catch (LocalizedException $e) {
                throw new GraphQlInputException(__($e->getMessage()), $e);
            }
        }

        return [
            'cart' => [
                'model' => $cart
            ],
            'response' => __('Gift message for order items was applied.')
        ];
    }
}
