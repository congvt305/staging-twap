<?php

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Api\FeeRepositoryInterface;
use Amasty\CheckoutGiftWrap\Api\GiftWrapInformationManagementInterface;
use Amasty\CheckoutGiftWrap\Model\ConfigProvider;
use Amasty\CheckoutGraphQl\Model\Utils\CartProvider;
use Magento\Framework\App\ObjectManager;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Module\Manager;

class UpdateGiftWrapInformation implements ResolverInterface
{
    public const GW_MODULE = 'Amasty_CheckoutGiftWrap';

    public const CHECKED_KEY = 'checked';

    /**
     * @var CartProvider
     */
    private $cartProvider;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(
        CartProvider $cartProvider,
        Manager $moduleManager,
        FeeRepositoryInterface $feeRepository
    ) {
        $this->cartProvider = $cartProvider;
        $this->moduleManager = $moduleManager;
        $this->feeRepository = $feeRepository;
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
        if (!$this->moduleManager->isEnabled(self::GW_MODULE)) {
            throw new GraphQlInputException(__('Gift wrap is not allowed.'));
        }

        $gwConfigProvider = ObjectManager::getInstance()->get(ConfigProvider::class);
        if (!$gwConfigProvider->isGiftWrapEnabled()) {
            throw new GraphQlInputException(__('Gift wrap is not allowed.'));
        }

        if (empty($args['input'][CartProvider::CART_ID_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', CartProvider::CART_ID_KEY));
        }

        if (!isset($args['input'][self::CHECKED_KEY])) {
            throw new GraphQlInputException(__('Required parameter "%1" is missing', self::CHECKED_KEY));
        }

        $cart = $this->cartProvider->getCartForUser($args['input'][CartProvider::CART_ID_KEY], $context);

        try {
            $gwInformationManagement = ObjectManager::getInstance()->get(GiftWrapInformationManagementInterface::class);
            $gwInformationManagement->update($cart->getId(), $args['input'][self::CHECKED_KEY]);
            $fee = $this->feeRepository->getByQuoteId($cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'cart' => [
                'model' => $cart
            ],
            'response' => __('Gift wrap status was changed.'),
            'amount' => $fee->getAmount(),
            'base_amount' => $fee->getBaseAmount()
        ];
    }
}
