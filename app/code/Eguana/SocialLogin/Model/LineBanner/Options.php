<?php

namespace Eguana\SocialLogin\Model\LineBanner;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;

/**
 * Class Options
 */
class Options implements  \Magento\Framework\Option\ArrayInterface
{
    /**
     * Block collection factory
     *
     * @var CollectionFactory
     */
    protected $_blockCollectionFactory;

    /**
     * @param CollectionFactory $_blockCollectionFactory
     */
    public function __construct(CollectionFactory $_blockCollectionFactory)
    {
        $this->_blockCollectionFactory = $_blockCollectionFactory;
    }

    /**
     * @return array
     */
    public function toOptionArray()
    {
        $options = $this->_blockCollectionFactory->create()->load()->toOptionArray();
        array_unshift($options, ['value' => '', 'label' => __('Please select a static block.')]);
        return $options;
    }

}
