<?php

namespace CJ\CustomCookie\Model\Config\Source;

use Magento\Cms\Model\ResourceModel\Block\CollectionFactory;
use Magento\Framework\Data\OptionSourceInterface;

/**
 * To provide cms block options in form
 *
 * Class CmsBlocks
 */
class CmsBlocks implements OptionSourceInterface
{
    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @param CollectionFactory $collectionFactory
     */
    public function __construct(
        CollectionFactory $collectionFactory
    ) {
        $this->collectionFactory = $collectionFactory;
    }

    /**
     * Return block options array
     *
     * @return array
     */
    public function toOptionArray() : array
    {
        $cmsBlockCollection = $this->collectionFactory->create();
        $options = [
            ['value' => '', 'label' => __('Select Block')]
        ];
        foreach ($cmsBlockCollection as $cmsBlock) {
            $options[] = [
                'value' => $cmsBlock->getIdentifier(),
                'label' => $cmsBlock->getTitle()
            ];
        }
        return $options;
    }
}
