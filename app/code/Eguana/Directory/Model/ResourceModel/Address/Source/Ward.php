<?php

namespace Eguana\Directory\Model\ResourceModel\Address\Source;

use Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory;

class Ward extends \Magento\Eav\Model\Entity\Attribute\Source\Table
{
    /**
     * @var CollectionFactory
     */
    private $wardCollectionFactory;

    public function __construct(
        \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory $wardCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\Option\CollectionFactory $attrOptionCollectionFactory,
        \Magento\Eav\Model\ResourceModel\Entity\Attribute\OptionFactory $attrOptionFactory
    ) {
        parent::__construct($attrOptionCollectionFactory, $attrOptionFactory);
        $this->wardCollectionFactory = $wardCollectionFactory;
    }

    public function getAllOptions($withEmpty = true, $defaultValues = false)
    {
        if (!$this->_options) {
            $this->_options = $this->wardCollectionFactory->create()
                ->load()->toOptionArray();
        }
        return $this->_options;
    }

}
