<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Review\GetAggregateRating;

use Amasty\SeoRichData\Model\Review\FormatRating;
use Amasty\SeoRichData\Model\Review\GetBestRating;

class GenerateRatingRichData
{
    /**
     * @var FormatRating
     */
    private $formatRating;

    /**
     * @var GetBestRating
     */
    private $getBestRating;

    public function __construct(FormatRating $formatRating, GetBestRating $getBestRating)
    {
        $this->formatRating = $formatRating;
        $this->getBestRating = $getBestRating;
    }

    /**
     * @param int $reviewCount
     * @param float $ratingValue
     * @param float $fromBestRating
     * @param int $formatRating
     * @return array
     */
    public function execute(int $reviewCount, float $ratingValue, float $fromBestRating, int $formatRating): array
    {
        $bestRating = $this->getBestRating->execute($formatRating);
        $ratingValue = $this->formatRating->execute($ratingValue, $fromBestRating, $bestRating);

        return [
            '@type' => 'AggregateRating',
            'ratingValue' => $ratingValue,
            'bestRating' => $bestRating,
            'reviewCount' => $reviewCount
        ];
    }
}
