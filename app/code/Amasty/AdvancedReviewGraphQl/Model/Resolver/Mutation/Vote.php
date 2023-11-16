<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Mutation;

use Amasty\AdvancedReview\Helper\Config as ConfigHelper;
use Amasty\AdvancedReview\Model\Repository\VoteRepository;
use Amasty\AdvancedReview\Model\Vote as VoteModel;
use Amasty\AdvancedReview\Model\VoteFactory;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\HTTP\PhpEnvironment\RemoteAddress;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as OptionVoteCollectionFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;

class Vote implements ResolverInterface
{
    /**
     * @var RemoteAddress
     */
    private $remoteAddress;

    /**
     * @var VoteRepository
     */
    private $voteRepository;

    /**
     * @var VoteFactory
     */
    private $voteFactory;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var OptionVoteCollectionFactory
     */
    private $optionVoteCollectionFactory;

    public function __construct(
        VoteRepository $voteRepository,
        VoteFactory $voteFactory,
        OptionVoteCollectionFactory $optionVoteCollectionFactory,
        ConfigHelper $configHelper,
        RemoteAddress $remoteAddress,
        ReviewFactory $reviewFactory
    ) {
        $this->remoteAddress = $remoteAddress;
        $this->voteRepository = $voteRepository;
        $this->voteFactory = $voteFactory;
        $this->configHelper = $configHelper;
        $this->reviewFactory = $reviewFactory;
        $this->optionVoteCollectionFactory = $optionVoteCollectionFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     * @throws GraphQlInputException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $result = [
            'success' => false
        ];

        if ($this->configHelper->isAllowHelpful()) {
            $voteData = $args['input'];

            $this->validateVoteData($voteData);
            $vote = $this->createVote($voteData);

            $result['success'] = true;
            $result['review'] = $this->getReview(
                (int) $vote->getReviewId(),
                (int) $context->getExtensionAttributes()->getStore()->getId()
            )->getData();
        }

        return $result;
    }

    /**
     * @param array $voteData
     * @return VoteModel
     * @throws CouldNotDeleteException
     * @throws CouldNotSaveException
     */
    private function createVote(array $voteData): VoteModel
    {
        $reviewId = $voteData['review_id'];
        $type = $voteData['type'];

        $ip = $this->remoteAddress->getRemoteAddress();

        $type = ($type == 'plus') ? '1' : '0';

        /** @var  VoteModel $model */
        $model = $this->voteRepository->getByIdAndIp($reviewId, $ip);
        $modelType = $model->getType();
        if ($model->getVoteId()) {
            $this->voteRepository->delete($model);
        }

        if ($modelType === null || $modelType != $type) {
            $model = $this->voteFactory->create();
            $model->setIp($ip);
            $model->setReviewId($reviewId);
            $model->setType($type);
            $this->voteRepository->save($model);
        }

        return $model;
    }

    /**
     * @param array $voteData
     * @throws GraphQlInputException
     */
    private function validateVoteData(array $voteData): void
    {
        $reviewId = $voteData['review_id'];
        if ($reviewId <= 0) {
            throw new GraphQlInputException(__('Review ID must be greater than 0.'));
        }

        $type = $voteData['type'];
        if (!in_array($type, ['plus', 'minus'])) {
            throw new GraphQlInputException(__('Vote type must be plus or minus.'));
        }
    }

    private function getReview(int $reviewId, int $storeId): Review
    {
        $review = $this->reviewFactory->create()->load($reviewId);

        $this->addVotes($review, $storeId);

        return $review;
    }

    private function addVotes(Review $review, int $storeId): void
    {
        $votes = $this->voteRepository->getVotesCount($review->getId());
        $review->setData('plus_review', $votes['plus'] ?? 0);
        $review->setData('minus_review', $votes['minus'] ?? 0);
        $votes = $this->optionVoteCollectionFactory->create()->setReviewFilter($review->getId())
            ->setStoreFilter($storeId)
            ->addRatingInfo($storeId);
        $review->setRatingVotes($votes->getItems());
    }
}
