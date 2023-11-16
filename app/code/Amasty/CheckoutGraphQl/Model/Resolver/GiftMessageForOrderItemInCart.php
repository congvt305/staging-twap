<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\ItemRepositoryInterface;
use Magento\GiftMessage\Api\Data\MessageInterface;

class GiftMessageForOrderItemInCart implements ResolverInterface
{
    /**
     * @var ItemRepositoryInterface
     */
    private $gmItemRepository;

    public function __construct(ItemRepositoryInterface $gmItemRepository)
    {
        $this->gmItemRepository = $gmItemRepository;
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

        /** @var \Magento\Quote\Model\Quote\Item $cartItem */
        $cartItem = $value['model'];

        try {
            /** @var MessageInterface $message */
            $message = $this->gmItemRepository->get($cartItem->getQuoteId(), $cartItem->getItemId());
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
