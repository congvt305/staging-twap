<?php
namespace CJ\AmastyReview\Model\ResourceModel\Images;


/**
 * This class returns collection of any entity
 *
 * Class Collection
 */
class Collection extends \Amasty\AdvancedReview\Model\ResourceModel\Images\Collection
{

    /**
     * @return $this
     */
    public function getReviewData()
    {
        $connection = $this->getConnection();
        $likeCnt = new \Zend_Db_Expr(
            '(SELECT count(1) FROM amasty_advanced_review_vote
                          WHERE ' . $connection->quoteInto('type = ?', 1) . ' AND review_id = main_table.review_id)'
        );
        $hateCnt = new \Zend_Db_Expr(
            '(SELECT count(1) FROM amasty_advanced_review_vote
                          WHERE ' . $connection->quoteInto('type = ?', 0) . ' AND review_id = main_table.review_id)'
        );

        $columns = [
            'main_table.review_id',
            'rvc.email as email',
            'rvd.title as title',
            'rv.entity_pk_value as product_id',
            'rvc.created_at',
            'main_table.path as photo_url',
            'rvd.detail as content',
            $likeCnt . ' as like_cnt',
            $hateCnt . ' as hate_cnt',
        ];
        $this->getSelect()
            ->join(
                ['rv' => 'review'],
                'rv.review_id = main_table.review_id'
            )
            ->join(
                ['rvd' => 'review_detail'],
                'rv.review_id = rvd.review_id'
            )
            ->join(
                ['rvc' => 'amasty_advanced_review_comments'],
                'main_table.review_id = rvc.review_id'
            )
            ->reset('columns')
            ->columns($columns)
            ->where('rvd.customer_id > ?', 0);
        return $this;
    }

}
