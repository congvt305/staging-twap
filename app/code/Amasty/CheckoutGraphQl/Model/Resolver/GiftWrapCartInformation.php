<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Api\FeeRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GiftWrapCartInformation implements ResolverInterface
{
    /**
     * @var FeeRepositoryInterface
     */
    private $feeRepository;

    public function __construct(FeeRepositoryInterface $feeRepository)
    {
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
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value must be specified'));
        }

        $cart = $value['model'];

        try {
            $fee = $this->feeRepository->getByQuoteId($cart->getId());
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            'amount' => $fee->getAmount(),
            'base_amount' =>  $fee->getBaseAmount(),
            'currency_code' => $fee->getAmount() ? $cart->getQuoteCurrencyCode() : null
        ];
    }
}
