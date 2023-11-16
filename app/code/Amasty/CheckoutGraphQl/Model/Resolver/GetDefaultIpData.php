<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Model\FieldsDefaultProvider;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class GetDefaultIpData implements ResolverInterface
{
    /**
     * @var FieldsDefaultProvider
     */
    private $fieldsDefaultProvider;

    public function __construct(FieldsDefaultProvider $fieldsDefaultProvider)
    {
        $this->fieldsDefaultProvider = $fieldsDefaultProvider;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        return $this->fieldsDefaultProvider->getDefaultData();
    }
}
