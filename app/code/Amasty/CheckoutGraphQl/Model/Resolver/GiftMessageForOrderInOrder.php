<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\GiftMessage\Api\OrderRepositoryInterface;
use Magento\GiftMessage\Api\Data\MessageInterface;

class GiftMessageForOrderInOrder implements ResolverInterface
{
    /**
     * @var OrderRepositoryInterface
     */
    private $gmOrderRepository;

    public function __construct(OrderRepositoryInterface $gmOrderRepository)
    {
        $this->gmOrderRepository = $gmOrderRepository;
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

        /** @var \Magento\Sales\Model\Order $order */
        $order = $value['model'];

        try {
            /** @var MessageInterface $message */
            $message = $this->gmOrderRepository->get($order->getEntityId());
        } catch (LocalizedException $e) {
            unset($e);
            return null;
        }

        return [
            'message' => $message->getMessage(),
            'sender' => $message->getSender(),
            'recipient' => $message->getRecipient()
        ];
    }
}
