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
use Magento\GiftMessage\Api\CartRepositoryInterface;

class AddGiftMessageForWholeOrder implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var CartRepositoryInterface
     */
    private $gmCartRepository;

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
        CartRepositoryInterface $gmCartRepository,
        GiftMessageProvider $giftMessageProvider,
        ConfigProvider $configProvider
    ) {
        $this->cartProvider = $cartProvider;
        $this->gmCartRepository = $gmCartRepository;
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
        if (!$this->configProvider->isAllowOrderGiftMessage()) {
            throw new GraphQlInputException(__('Gift message for whole order is not allowed.'));
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);
        $message = $this->giftMessageProvider->prepareGiftMessage($args['input']);

        try {
            $this->gmCartRepository->save($cart->getId(), $message);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart
            ],
            'response' => __('Gift message for whole order was applied.')
        ];
    }
}
