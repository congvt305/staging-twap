<?php

namespace CJ\Sorting\Plugin\Elasticsearch\Model\Adapter;

use Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapperProxy;
use Magento\Elasticsearch\Model\Adapter\FieldMapper\Product\FieldProvider\FieldType\ConverterInterface;
use Magento\Store\Model\StoreManagerInterface;

class AdditionalFieldMapper
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $_storeManager;

    /**
     * Toolbar constructor.
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        StoreManagerInterface $storeManager
    ) {
        $this->_storeManager = $storeManager;
    }

    /**
     * Sets `created_at` type keyword to enable sorting
     *
     * @param ProductFieldMapperProxy $subject
     * @param array $allAttributes
     * @return array
     * @see \Magento\Elasticsearch\Elasticsearch5\Model\Adapter\FieldMapper\ProductFieldMapper::getAllAttributesTypes
     */
    public function afterGetAllAttributesTypes($subject, array $allAttributes)
    {
        if ($this->_storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $allAttributes['created_at']['type'] = 'keyword';
        }

        return $allAttributes;
    }
}
