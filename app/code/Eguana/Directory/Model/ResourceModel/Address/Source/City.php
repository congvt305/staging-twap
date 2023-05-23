<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/14/20
 * Time: 6:27 AM
 */

namespace Eguana\Directory\Model\ResourceModel\Address\Source;


use Eguana\Directory\Model\ResourceModel\City\CollectionFactory;

class City extends \Magento\Eav\Model\Entity\Attribute\Source\Table
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
            $this->_options[] = ['title' => '', 'value' => '0000', 'label' => __('Please select a city or district.')];
        }
        return $this->_options;
    }

}
