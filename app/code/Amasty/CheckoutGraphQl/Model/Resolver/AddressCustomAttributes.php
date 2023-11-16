<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Model\ResourceModel\Field\CollectionFactory;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;

class AddressCustomAttributes implements ResolverInterface
{
    /**
     * @var CollectionFactory
     */
    private $fieldCollectionFactory;

    public function __construct(CollectionFactory $fieldCollectionFactory)
    {
        $this->fieldCollectionFactory = $fieldCollectionFactory;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        if (!isset($value['model'])) {
            throw new GraphQlInputException(__('"model" value should be specified'));
        }

        $result = [];
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();
        $fieldCollection = $this->fieldCollectionFactory->create()->getAttributeCollectionByStoreId($storeId);
        foreach ($fieldCollection->getItems() as $field) {
            $attrCode = $field->getAttributeCode();
            if (array_key_exists($attrCode, $value)) {
                if (is_array($value[$attrCode])) {
                    $value[$attrCode] = implode(",", $value[$attrCode]);
                }
                $result[] = [
                    'attribute_code' => (string)$attrCode,
                    'value' => (string)$value[$attrCode]
                ];
            }
        }

        return $result;
    }
}
