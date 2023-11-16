<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Quote\Api\Data\ShippingMethodInterface;
use Magento\Quote\Api\ShippingMethodManagementInterface;

class GetAvailableShippingMethods implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var Json
     */
    private $jsonSerializer;

    /**
     * @var ShippingMethodManagementInterface
     */
    private $shippingMethodManagement;

    public function __construct(
        CartProvider $cartProvider,
        Json $jsonSerializer,
        ShippingMethodManagementInterface $shippingMethodManagement
    ) {
        $this->cartProvider = $cartProvider;
        $this->jsonSerializer = $jsonSerializer;
        $this->shippingMethodManagement = $shippingMethodManagement;
    }

    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (empty($args[CartProvider::CART_ID])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID));
        }

        $availableMethodsArray = [];
        $cart = $this->cartProvider->getCartForUser($args[CartProvider::CART_ID], $context);

        try {
            $availableMethods = $this->shippingMethodManagement->getList((int)$cart->getId());
            foreach ($availableMethods as $id => $method) {
                $availableMethodsArray[$id][ShippingMethodInterface::KEY_CARRIER_CODE] = $method->getCarrierCode();
                $availableMethodsArray[$id][ShippingMethodInterface::KEY_METHOD_CODE] = $method->getMethodCode();
            }
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'available_shipping_methods' => (string)$this->jsonSerializer->serialize($availableMethodsArray)
        ];
    }
}
