<?php
declare(strict_types=1);

namespace CJ\ReviewsImportExport\Model\Import\Behaviors;

class AddUpdate extends \Amasty\ReviewsImportExport\Model\Import\Behaviors\AddUpdate
{
    /**
     * Customize get vote id when update import
     *
     * @param int $reviewId
     * @param array $reviewData
     * @param bool $isUpdate
     */
    protected function saveRating($reviewId, $reviewData, $isUpdate)
    {
        $voteId = $isUpdate
            ? $this->ratingOptionCollection->getItemByColumnValue('review_id', $reviewId)->getVoteId()
            : null;

        $optionIds = isset($reviewData['option_ids']) && $reviewData['option_ids']
            ? explode(',', $reviewData['option_ids'])
            : false;
        $ratingIds = isset($reviewData['rating_ids']) && $reviewData['rating_ids']
            ? explode(',', $reviewData['rating_ids'])
            : false;
        if ($optionIds && $ratingIds) {
            $rating = $this->ratingFactory->create()->setReviewId($reviewId);
            foreach ($optionIds as $key => $id) {
                if ($voteId) {
                    $rating->setVoteId($voteId)->updateOptionVote($id);
                } else {
                    $rating->setRatingId($ratingIds[$key])
                        ->setReviewId($reviewId)
                        ->setCustomerId($reviewData['customer_id'] ?? 0)
                        ->addOptionVote($id, $reviewData['entity_pk_value'] ?? 0);
                }
            }
        }
    }
}
