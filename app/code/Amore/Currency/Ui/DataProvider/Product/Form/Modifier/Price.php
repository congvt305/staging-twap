<?php
declare(strict_types=1);

namespace Amore\Currency\Ui\DataProvider\Product\Form\Modifier;

use Magento\Catalog\Api\Data\ProductAttributeInterface;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;
use Magento\Framework\Stdlib\ArrayManager;

class Price extends AbstractModifier
{
    const CODE_GROUP_PRICE = 'container_price';

    /**
     * @var ArrayManager
     */
    private $arrayManager;

    /**
     * Price constructor.
     * @param ArrayManager $arrayManager
     */
    public function __construct(
        ArrayManager $arrayManager
    ) {
        $this->arrayManager = $arrayManager;
    }

    /**
     * @inheritdoc
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * @inheritdoc
     */
    public function modifyMeta(array $meta)
    {
        $meta = $this->arrayManager->merge(
            $this->arrayManager->findPath(ProductAttributeInterface::CODE_SPECIAL_PRICE, $meta),
            $meta,
            [
                'arguments' => [
                    'data' => [
                        'config' => [
                            'validation' => [
                                'validate-number-integer' => true
                            ]
                        ],
                    ],
                ],
            ]
        );
        $groupCode = $this->getGroupCodeByField($meta, ProductAttributeInterface::CODE_PRICE)
            ?: $this->getGroupCodeByField($meta, self::CODE_GROUP_PRICE);

        if ($groupCode && !empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
            if (!empty($meta[$groupCode]['children'][self::CODE_GROUP_PRICE])) {
                $meta[$groupCode]['children'][self::CODE_GROUP_PRICE] = array_replace_recursive(
                    $meta[$groupCode]['children'][self::CODE_GROUP_PRICE],
                    [
                        'children' => [
                            ProductAttributeInterface::CODE_PRICE => [
                                'arguments' => [
                                    'data' => [
                                        'config' => [
                                            'validation' => [
                                                'validate-number-integer' => true
                                            ]
                                        ],
                                    ],
                                ],
                            ],
                        ],
                    ]
                );
            }
        }

        return $meta;
    }
}
