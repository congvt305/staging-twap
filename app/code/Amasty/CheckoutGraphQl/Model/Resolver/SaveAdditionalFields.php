<?php

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Api\AdditionalFieldsManagementInterface;
use Amasty\CheckoutGraphQl\Model\Utils\AdditionalFieldsProvider;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class SaveAdditionalFields implements ResolverInterface
{
    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var AdditionalFieldsProvider
     */
    private $additionalFieldsProvider;

    /**
     * @var AdditionalFieldsManagementInterface
     */
    private $fieldsManagement;

    public function __construct(
        CartProvider $cartProvider,
        AdditionalFieldsProvider $additionalFieldsProvider,
        AdditionalFieldsManagementInterface $fieldsManagement
    ) {
        $this->cartProvider = $cartProvider;
        $this->additionalFieldsProvider = $additionalFieldsProvider;
        $this->fieldsManagement = $fieldsManagement;
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
        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        $fields = $this->additionalFieldsProvider->prepareAdditionalFields($args['input']);
        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $this->fieldsManagement->save($cart->getId(), $fields);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart,
            ],
            'response' => __('Additional fields were saved.')
        ];
    }
}
