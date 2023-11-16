<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review\GetReviews;

use Amasty\SeoRichData\Model\Review\FormatRating;
use Amasty\SeoRichData\Model\Review\GetBestRating;
use Amasty\SeoRichData\Model\Review\ReviewBuilder;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;
use Magento\Review\Model\ResourceModel\Review\CollectionFactory as ReviewCollectionFactory;
use Magento\Review\Model\Review;

class GetMagentoReviews implements ReviewProviderInterface
{
    /**
     * @var ReviewCollectionFactory
     */
    private $reviewCollectionFactory;

    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var ReviewBuilder
     */
    private $reviewBuilder;

    /**
     * @var GenerateReviewRichData
     */
    private $generateReviewRichData;

    /**
     * @var FormatRating
     */
    private $formatRating;

    /**
     * @var GetBestRating
     */
    private $getBestRating;

    public function __construct(
        ReviewCollectionFactory $reviewCollectionFactory,
        RatingFactory $ratingFactory,
        ReviewBuilder $reviewBuilder,
        GenerateReviewRichData $generateReviewRichData,
        FormatRating $formatRating,
        GetBestRating $getBestRating
    ) {
        $this->reviewCollectionFactory = $reviewCollectionFactory;
        $this->ratingFactory = $ratingFactory;
        $this->reviewBuilder = $reviewBuilder;
        $this->generateReviewRichData = $generateReviewRichData;
        $this->formatRating = $formatRating;
        $this->getBestRating = $getBestRating;
    }

    /**
     * @param int $productId
     * @param int $storeIdFilter
     * @param int $numberReviews
     * @param int $formatRating
     * @return array
     */
    public function execute(int $productId, int $storeIdFilter, int $numberReviews, int $formatRating): array
    {
        $reviews = [];

        $reviewCollection = $this->getReviewCollection($productId, $storeIdFilter, $numberReviews);
        /** @var Review $review */
        foreach ($reviewCollection->getItems() as $review) {
            $rating = $this->ratingFactory->create()->getReviewSummary($review->getId(), true);
            if ($rating->getSum() === null) {
                $bestRating = 0;
                $ratingValue = 0;
            } else {
                $bestRating = $this->getBestRating->execute($formatRating);
                $ratingValue = $this->formatRating->execute(
                    (float) $rating->getSum(),
                    $rating->getCount() * 100,
                    $bestRating
                );
            }

            $this->reviewBuilder->setNickname($review->getNickname());
            $this->reviewBuilder->setCreatedAt($review->getCreatedAt());
            $this->reviewBuilder->setTitle($review->getTitle());
            $this->reviewBuilder->setDetail($review->getDetail());
            $this->reviewBuilder->setRatingValue($ratingValue);
            $this->reviewBuilder->setBestRating($bestRating);

            $reviews[] = $this->generateReviewRichData->execute($this->reviewBuilder->create());
        }

        return $reviews;
    }

    private function getReviewCollection(int $productId, int $storeIdFilter, int $numberReviews): ReviewCollection
    {
        $reviewCollection = $this->reviewCollectionFactory->create();
        $reviewCollection->addStoreFilter($storeIdFilter);
        $reviewCollection->addStatusFilter(Review::STATUS_APPROVED);
        $reviewCollection->addEntityFilter(Review::ENTITY_PRODUCT_CODE, $productId);
        $reviewCollection->setDateOrder();
        $reviewCollection->setPageSize($numberReviews);

        return $reviewCollection;
    }
}
