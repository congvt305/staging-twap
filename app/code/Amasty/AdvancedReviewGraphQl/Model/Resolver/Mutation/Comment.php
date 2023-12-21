<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Mutation;

use Amasty\AdvancedReview\Api\CommentRepositoryInterface;
use Amasty\AdvancedReview\Api\Data\CommentInterface;
use Amasty\AdvancedReview\Helper\Config as ConfigHelper;
use Amasty\AdvancedReview\Model\Sources\CommentStatus;
use GraphQL\Validator\Rules\ValuesOfCorrectType;
use Magento\Customer\Model\CustomerFactory;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;

class Comment implements ResolverInterface
{
    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var CustomerFactory
     */
    private $customerFactory;

    public function __construct(
        CommentRepositoryInterface $commentRepository,
        CustomerFactory $customerFactory,
        ConfigHelper $configHelper,
        SessionManagerInterface $sessionManager,
        ReviewFactory $reviewFactory
    ) {
        $this->commentRepository = $commentRepository;
        $this->configHelper = $configHelper;
        $this->sessionManager = $sessionManager;
        $this->reviewFactory = $reviewFactory;
        $this->customerFactory = $customerFactory;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return bool[]|Value|mixed
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $result = [
            'success' => false
        ];

        if ($this->configHelper->isCommentsEnabled()) {
            $commentData = $args['input'];

            $comment = $this->createComment($commentData, $context);

            $result['success'] = true;
            $result['review'] = $this->getReview(
                (int) $comment->getReviewId()
            )->getData();
        }

        return $result;
    }

    /**
     * @param array $commentData
     * @param ContextInterface $context
     * @return CommentInterface
     * @throws GraphQlInputException
     * @throws GraphQlNoSuchEntityException
     * @throws LocalizedException
     */
    private function createComment(array $commentData, ContextInterface $context): CommentInterface
    {
        $commentData = $this->prepareCommentData($commentData, $context->getExtensionAttributes()->getIsCustomer());

        $comment = $this->commentRepository->getComment();
        $comment->addData($commentData);

        if ($this->configHelper->isCommentApproved()) {
            $comment->setStatus(CommentStatus::STATUS_APPROVED);
        } else {
            $comment->setStatus(CommentStatus::STATUS_PENDING);
        }

        if ($context->getExtensionAttributes()->getIsCustomer()) {
            $customer = $this->customerFactory->create()->load($context->getUserId());
            if ($customer->getId()) {
                $comment->setEmail($customer->getEmail());
                $comment->setCustomerId($customer->getId());
                $comment->setNickname($customer->getName());
            }
        }

        $comment->setSessionId($this->sessionManager->getSessionId());
        $comment->setStoreId($context->getExtensionAttributes()->getStore()->getId());

        return $this->commentRepository->save($comment);
    }

    /**
     * @param array $commentData
     * @param bool $isCustomer
     * @return array
     * @throws GraphQlInputException
     */
    private function prepareCommentData(array $commentData, bool $isCustomer): array
    {
        if ($isCustomer) {
            unset($commentData[CommentInterface::NICKNAME]);
            unset($commentData[CommentInterface::EMAIL]);
        } else {
            $this->validateField($commentData, CommentInterface::NICKNAME, 'String');
            $this->validateField($commentData, CommentInterface::EMAIL, 'String');
        }

        return $commentData;
    }

    /**
     * @param array $data
     * @param string $field
     * @param string $type
     * @throws GraphQlInputException
     */
    private function validateField(array $data, string $field, string $type): void
    {
        if (!isset($data[$field]) || empty($data[$field])) {
            throw new GraphQlInputException(__(ValuesOfCorrectType::requiredFieldMessage(
                'AddAdvCommentInput',
                $field,
                $type
            )));
        }
    }

    private function getReview(int $reviewId): Review
    {
        $review = $this->reviewFactory->create()->load($reviewId);

        $this->addComments($review);

        return $review;
    }

    private function addComments(Review $review): void
    {
        $review->setComments($this->commentRepository->getListByReviewId($review->getId())->getItems());
    }
}
