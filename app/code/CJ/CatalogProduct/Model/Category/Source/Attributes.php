<?php
declare(strict_types=1);

namespace CJ\CatalogProduct\Model\Category\Source;

use Magento\Eav\Model\ResourceModel\Entity\Attribute\CollectionFactory;

/**
 * Class Attributes
 */
class Attributes extends \Magento\Eav\Model\Entity\Attribute\Source\AbstractSource
{
    /**
     * @var CollectionFactory
     */
    protected CollectionFactory $attributeCollectionFactory;

    /**
     * @param CollectionFactory $attributeCollectionFactory
     */
    public function __construct(CollectionFactory $attributeCollectionFactory)
    {
        $this->attributeCollectionFactory = $attributeCollectionFactory;
    }

    /**
     * @inheritdoc
     */
    public function getAllOptions()
    {
        if ($this->_options === null) {
            $this->_options = [];

            $attributeCollection = $this->attributeCollectionFactory->create();
            $attributeCollection->addFieldToSelect(['attribute_code', 'attribute_id', 'frontend_label']);
            $attributeCollection->addOrder('attribute_code', 'ASC');
            foreach ($attributeCollection as $attribute) {
                $this->_options[] = [
                    'label' => __("{$attribute['frontend_label']} (code: {$attribute['attribute_code']})"),
                    'value' => $attribute['attribute_code']
                ];
            }
        }

        return $this->_options;
    }
}
