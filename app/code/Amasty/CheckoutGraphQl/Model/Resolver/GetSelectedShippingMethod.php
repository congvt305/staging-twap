<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetSelectedShippingMethod implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    public function __construct(
        CartProvider $cartProvider
    ) {
        $this->cartProvider = $cartProvider;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args[CartProvider::CART_ID])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID));
        }

        $cart = $this->cartProvider->getCartForUser($args[CartProvider::CART_ID], $context);
        $shippingMethod = $cart->getShippingAddress()->getShippingMethod();
        [$carrierCode, $methodCode] = explode('_', $shippingMethod, 2);

        return [
            'carrier_code' => $carrierCode,
            'method_code' => $methodCode
        ];
    }
}
