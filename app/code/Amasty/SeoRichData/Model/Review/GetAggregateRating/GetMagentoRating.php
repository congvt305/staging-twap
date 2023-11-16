<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review\GetAggregateRating;

use Magento\Catalog\Model\Product;
use Magento\Review\Model\ReviewSummaryFactory;

class GetMagentoRating implements RatingProviderInterface
{
    /**
     * @var GenerateRatingRichData
     */
    private $generateRatingRichData;

    /**
     * @var ReviewSummaryFactory
     */
    private $reviewSummaryFactory;

    public function __construct(
        ReviewSummaryFactory $reviewSummaryFactory,
        GenerateRatingRichData $generateRatingRichData
    ) {
        $this->generateRatingRichData = $generateRatingRichData;
        $this->reviewSummaryFactory = $reviewSummaryFactory;
    }

    /**
     * @param Product $product
     * @param int $formatRating
     * @return array
     */
    public function execute(Product $product, int $formatRating): array
    {
        if ($product->getRatingSummary() === null) {
            $reviewSummary = $this->reviewSummaryFactory->create();
            $reviewSummary->appendSummaryDataToObject($product, $product->getStoreId());
        }

        $ratingSummary = $product->getRatingSummary();
        $ratingValue = $ratingSummary['rating_summary'] ?? $ratingSummary;
        $reviewCount = $ratingSummary['reviews_count'] ?? $product->getReviewsCount();

        if ($ratingValue && $reviewCount) {
            $rating = $this->generateRatingRichData->execute(
                (int) $reviewCount,
                (float) $ratingValue,
                100,
                $formatRating
            );
        } else {
            $rating = [];
        }

        return $rating;
    }
}
