<?php

namespace CJ\AmastyReview\Plugin\Block\Review\Product\View;

use Amasty\AdvancedReview\Block\Review\Product\View\ListView;
use CJ\AmastyReview\Helper\Data;
use Magento\Review\Model\ResourceModel\Review\Collection;

/**
 * Class AfterListView
 * @package CJ\AmastyReview\Plugin\Block\Review\Product\View
 */
class AfterListView
{
    /**
     * @var Data
     */
    protected Data $helperData;

    /**
     * @param Data $data
     */
    public function __construct(
        Data $data
    ) {
        $this->helperData = $data;
    }
    /**
     * @param ListView $subject
     * @param Collection $result
     * @return Collection
     */
    public function afterGetReviewsCollection(ListView $subject, Collection $result): Collection
    {
        if ($this->helperData->getCustomDisplayReview()) {
            $items = $result->getItems();
            if (!empty($items)) {
                foreach ($items as $keyItem => $valueItem) {
                    if ((int)$valueItem->getRatingSummary() < 4) {
                        $result->removeItemByKey($keyItem);
                    }
                }
            }
        }

        return $result;
    }
}
