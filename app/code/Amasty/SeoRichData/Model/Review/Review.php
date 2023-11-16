<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review;

class Review
{
    public const NICKNAME = 'nickname';
    public const CREATED_AT = 'created_at';
    public const TITLE = 'title';
    public const DETAIL = 'detail';
    public const RATING_VALUE = 'rating_value';
    public const BEST_RATING = 'best_rating';

    /**
     * @var array
     */
    private $data;

    public function __construct(array $data = [])
    {
        $this->data = $data;
    }

    /**
     * @return string
     */
    public function getNickname(): string
    {
        return $this->data[Review::NICKNAME];
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
     * @return string
     */
    public function getCreatedAt(): string
    {
        return $this->data[Review::CREATED_AT];
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
     * @return string
     */
    public function getTitle(): string
    {
        return $this->data[Review::TITLE];
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
     * @return string
     */
    public function getDetail(): string
    {
        return $this->data[Review::DETAIL];
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
     * @return float
     */
    public function getRatingValue(): float
    {
        return $this->data[Review::RATING_VALUE];
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
     * @return int
     */
    public function getBestRating(): int
    {
        return $this->data[Review::BEST_RATING];
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
