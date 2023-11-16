<?php
declare(strict_types=1);

namespace Amasty\CheckoutGraphQl\Model\Resolver;

use Amasty\CheckoutCore\Model\Field as CheckoutField;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json;

class ManageCheckoutFields implements ResolverInterface
{
    /**
     * @var CheckoutField
     */
    private $fieldSingleton;

    /**
     * @var Json
     */
    private $jsonSerializer;

    public function __construct(
        CheckoutField $fieldSingleton,
        Json $jsonSerializer
    ) {
        $this->fieldSingleton = $fieldSingleton;
        $this->jsonSerializer = $jsonSerializer;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $storeId = (int)$context->getExtensionAttributes()->getStore()->getId();

        return [
            'amasty_checkout_checkout_fields' => $this->getCheckoutFields($storeId)
        ];
    }

    /**
     * @param int $storeId
     * @return string
     */
    private function getCheckoutFields(int $storeId): string
    {
        $fieldsData = [];
        $fieldsConfig = $this->fieldSingleton->getConfig($storeId);

        foreach ($fieldsConfig as $code => $field) {
            $fieldsData[$code] = $field->getData();
        }

        return (string)$this->jsonSerializer->serialize($fieldsData);
    }
}
