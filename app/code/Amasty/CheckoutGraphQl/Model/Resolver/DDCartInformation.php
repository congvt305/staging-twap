<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutGraphQl\Model\Utils\DDGetter;
use Amasty\CheckoutGraphQl\Model\Utils\DDTimeDisplayValueGetter;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Module\Manager;

class DDCartInformation implements ResolverInterface
{
    /**
     * @var DDGetter
     */
    private $ddGetter;

    /**
     * @var Manager
     */
    private $moduleManager;

    /**
     * @var DDTimeDisplayValueGetter
     */
    private $displayValueGetter;

    public function __construct(
        DDGetter $ddGetter,
        Manager $moduleManager,
        DDTimeDisplayValueGetter $displayValueGetter
    ) {
        $this->ddGetter = $ddGetter;
        $this->moduleManager = $moduleManager;
        $this->displayValueGetter = $displayValueGetter;
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

        if (!$this->moduleManager->isEnabled(DDGetter::DD_MODULE)) {
            throw new GraphQlInputException(__('Delivery Date isn\'t allowed'));
        }

        $cart = $value['model'];
        $cartId = (int)$cart->getId();

        try {
            $delivery = $this->ddGetter->getByQuoteId($cartId);
            $time = $delivery->getTime();
            $displayValue = $this->displayValueGetter->getDisplayValue($time);
        } catch (LocalizedException $e) {
            throw new GraphQlInputException(__($e->getMessage()), $e);
        }

        return [
            DDGetter::DATE_KEY => $delivery->getDate(),
            DDGetter::COMMENT_KEY => $delivery->getComment(),
            DDGetter::TIME_KEY => [
                'value' => $time,
                'displayValue' => $displayValue
            ]
        ];
    }
}
