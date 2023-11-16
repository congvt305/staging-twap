<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\CartRepositoryInterface;
use Magento\GiftMessage\Api\Data\MessageInterface;

class GiftMessageForOrderInCart implements ResolverInterface
{
    /**
     * @var CartRepositoryInterface
     */
    private $gmCartRepository;

    public function __construct(CartRepositoryInterface $gmCartRepository)
    {
        $this->gmCartRepository = $gmCartRepository;
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
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value must be specified'));
        }

        /** @var \Magento\Quote\Model\Quote $cart */
        $cart = $value['model'];

        try {
            /** @var MessageInterface $message */
            $message = $this->gmCartRepository->get($cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        if (!isset($message)) {
            return null;
        }

        return [
            'message' => $message->getMessage(),
            'sender' => $message->getSender(),
            'recipient' => $message->getRecipient()
        ];
    }
}
