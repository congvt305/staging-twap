<?php

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutDeliveryDate\Api\DeliveryInformationManagementInterface;
use Amasty\CheckoutDeliveryDate\Model\ConfigProvider;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Module\Manager;

class UpdateDeliveryInformation implements ResolverInterface
{
    public const DD_MODULE = 'Amasty_CheckoutDeliveryDate';

    public const DATE_KEY = 'date';
    public const TIME_KEY = 'time';
    public const COMMENT_KEY = 'comment';

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var Manager
     */
    private $moduleManager;

    public function __construct(
        CartProvider $cartProvider,
        Manager $moduleManager
    ) {
        $this->cartProvider = $cartProvider;
        $this->moduleManager = $moduleManager;
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
        if (!$this->moduleManager->isEnabled(self::DD_MODULE)) {
            throw new GraphQlInputException(__('Delivery date is not allowed.'));
        }

        $ddConfigProvider = ObjectManager::getInstance()->get(ConfigProvider::class);
        if (!$ddConfigProvider->isDeliveryDateEnabled()) {
            throw new GraphQlInputException(__('Delivery date is not allowed.'));
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        if (empty($args['input'][self::DATE_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', self::DATE_KEY));
        }

        $time = empty($args['input'][self::TIME_KEY]) ? '' : $args['input'][self::TIME_KEY];

        $comment = '';
        if ($ddConfigProvider->isCommentEnabled() && !empty($args['input'][self::COMMENT_KEY])) {
            $comment = $args['input'][self::COMMENT_KEY];
        }

        $date = $args['input'][self::DATE_KEY];
        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $ddInformationManagement = ObjectManager::getInstance()->get(DeliveryInformationManagementInterface::class);
            $ddInformationManagement->update($cart->getId(), $date, $time, $comment);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
            'response' => __('Delivery date was changed.')
        ];
    }
}
