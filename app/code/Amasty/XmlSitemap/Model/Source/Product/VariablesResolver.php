<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Source\Product;

use Magento\Catalog\Model\Product;

class VariablesResolver
{
    const VARIABLE_FORMAT = '{%s}';

    public function resolveString(Product $product, string $template): string
    {
        foreach ($this->getVariables() as $variable => $dataKey) {
            $wrappedVariable = sprintf(self::VARIABLE_FORMAT, $variable);

            if (strpos($template, $wrappedVariable) !== false) {
                $value = $product->getData($dataKey);

                if (!is_string($value)) {
                    $value = '';
                }
                $template = str_replace($wrappedVariable, $value, $template);
            }
        }

        return $template;
    }

    public function getVariables(): array
    {
        return [
            'product_name' => 'name'
        ];
    }
}
