<?php

namespace Amasty\SeoRichData\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Framework\DB\Select;
use Magento\Store\Model\StoreManagerInterface;

class Category extends AbstractHelper
{
    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @param Context               $context
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        Context $context,
        StoreManagerInterface $storeManager
    ) {
        $this->storeManager = $storeManager;

        return parent::__construct($context);
    }

    public function getMinimalPrice(\Magento\Catalog\Model\Category $category)
    {
        return $category->getProductCollection()->addMinimalPrice()->getMinPrice();
    }

    public function getReviewSummaryInfo(\Magento\Catalog\Model\Category $category)
    {
        $collection = $category->getProductCollection();
        $resource = $collection->getResource();

        $collection->getSelect()
            ->reset(Select::COLUMNS)
            ->reset(Select::ORDER)
            ->reset(Select::LIMIT_COUNT)
            ->reset(Select::LIMIT_OFFSET)
            ->join(
                ['summary' => $resource->getTable('review_entity_summary')],
                'summary.entity_pk_value = e.entity_id',
                ['rating' => 'AVG(rating_summary)', 'reviews' => 'SUM(reviews_count)']
            )
            ->where('summary.store_id = ?', $this->storeManager->getStore()->getId())
            ->where('reviews_count > 0')
        ;

        return $resource->getConnection()->fetchRow($collection->getSelect());
    }
}
