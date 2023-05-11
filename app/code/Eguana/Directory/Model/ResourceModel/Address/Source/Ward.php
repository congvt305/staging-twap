<?php

namespace Eguana\Directory\Model\ResourceModel\Address\Source;

use Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory;

class Ward extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{

    /**
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory
     * @param \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
     */
    public function __construct(
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
    }

    /**
     * Get all options
     *
     * @param boolean $withEmpty
     * @param boolean $defaultValues
     * @return array|null
     */
    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options[] = ['title' => '', 'value' => '0000', 'label' => __('Please select a ward')];
        }
        return $this->_options;
    }

}
