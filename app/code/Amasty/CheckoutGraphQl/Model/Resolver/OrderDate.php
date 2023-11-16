<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Api\Data\OrderInterface;

class OrderDate implements ResolverInterface
{
    /**
     * @var OrderInterface
     */
    private $orderModel;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(OrderInterface $orderModel, TimezoneInterface $timezone)
    {
        $this->orderModel = $orderModel;
        $this->timezone = $timezone;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return string|null
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['order_number'])) {
            throw new GraphQlInputException(__('"order_number" value must be specified'));
        }

        try {
            $order = $this->orderModel->loadByIncrementId($value['order_number']);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return $this->timezone->formatDate($order->getCreatedAt(), \IntlDateFormatter::MEDIUM);
    }
}
