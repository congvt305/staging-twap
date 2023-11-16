<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review;

class ReviewBuilder
{
    /**
     * @var array
     */
    private $data = [];

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    public function __construct(ReviewFactory $reviewFactory)
    {
        $this->reviewFactory = $reviewFactory;
    }

    /**
     * @return Review
     */
    public function create(): Review
    {
        $review = $this->reviewFactory->create(['data' => $this->data]);
        $this->data = [];

        return $review;
    }

    /**
     * @param string $nickname
     * @return void
     */
    public function setNickname(string $nickname): void
    {
        $this->data[Review::NICKNAME] = $nickname;
    }

    /**
     * @param string $createdAt
     * @return void
     */
    public function setCreatedAt(string $createdAt): void
    {
        $this->data[Review::CREATED_AT] = $createdAt;
    }

    /**
     * @param string $title
     * @return void
     */
    public function setTitle(string $title): void
    {
        $this->data[Review::TITLE] = $title;
    }

    /**
     * @param string $detail
     * @return void
     */
    public function setDetail(string $detail): void
    {
        $this->data[Review::DETAIL] = $detail;
    }

    /**
     * @param float $ratingValue
     * @return void
     */
    public function setRatingValue(float $ratingValue): void
    {
        $this->data[Review::RATING_VALUE] = $ratingValue;
    }

    /**
     * @param int $bestRating
     * @return void
     */
    public function setBestRating(int $bestRating): void
    {
        $this->data[Review::BEST_RATING] = $bestRating;
    }
}
