<?php

namespace CJ\SKUValidation\Ui\DataProvider\Product\Form\Modifier;
use Magento\Catalog\Ui\DataProvider\Product\Form\Modifier\AbstractModifier;

class SkuFields extends AbstractModifier
{

    /**
     * @param array $data
     * @return array
     */
    public function modifyData(array $data)
    {
        return $data;
    }

    /**
     * Validation no whitespace sku field
     *
     * @param array $meta
     * @return array
     */
    public function modifyMeta(array $meta)
    {
        $meta = array_replace_recursive(
            $meta,
            [
                'product-details' => [
                    'children' => [
                        'container_sku' => [
                            'children' => [
                                'sku' => [
                                    'arguments' => [
                                        'data' => [
                                            'config' => [
                                                'validation' => [
                                                    'required-entry' => true,
                                                    'no-marginal-whitespace' => true,
                                                    'no-whitespace' => true
                                                ]
                                            ]
                                        ]
                                    ]
                                ]
                            ]
                        ]
                    ],
                ]
            ]
        );
        return $meta;
    }

}
