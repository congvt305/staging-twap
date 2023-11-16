<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review\GetReviews;

interface ReviewProviderInterface
{
    /**
     * @param int $productId
     * @param int $storeIdFilter
     * @param int $numberReviews
     * @param int $formatRating
     * @return array
     */
    public function execute(int $productId, int $storeIdFilter, int $numberReviews, int $formatRating): array;
}
