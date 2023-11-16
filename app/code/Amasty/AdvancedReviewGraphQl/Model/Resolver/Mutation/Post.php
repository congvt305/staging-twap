<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Mutation;

use Amasty\AdvancedReview\Helper\Config as ConfigHelper;
use Amasty\AdvancedReview\Model\ResourceModel\Review as ReviewResource;
use Exception;
use GraphQL\Validator\Rules\ValuesOfCorrectType;
use Magento\Catalog\Api\Data\ProductInterface;
use Magento\Catalog\Api\ProductRepositoryInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Exception\GraphQlInputException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Query\Resolver\Value;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\Serialize\Serializer\Json as JsonSerializer;
use Magento\Review\Model\RatingFactory;
use Magento\Review\Model\Review;
use Magento\Review\Model\ReviewFactory;
use Psr\Log\LoggerInterface;

class Post implements ResolverInterface
{
    /**
     * @var ReviewFactory
     */
    private $reviewFactory;

    /**
     * @var ProductRepositoryInterface
     */
    private $productRepository;

    /**
     * @var RatingFactory
     */
    private $ratingFactory;

    /**
     * @var JsonSerializer
     */
    private $jsonSerializer;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var ConfigHelper
     */
    private $configHelper;

    /**
     * @var \Amasty\AdvancedReview\Model\ImageUploader
     */
    private $imageUploader;

    /**
     * @var \Amasty\AdvancedReview\Model\ImagesFactory
     */
    private $imagesFactory;

    /**
     * @var \Amasty\AdvancedReview\Model\Repository\ImagesRepository
     */
    private $imagesRepository;

    /**
     * @var ReviewResource
     */
    private $reviewResource;

    public function __construct(
        ConfigHelper $configHelper,
        ReviewFactory $reviewFactory,
        RatingFactory $ratingFactory,
        ProductRepositoryInterface $productRepository,
        JsonSerializer $jsonSerializer,
        LoggerInterface $logger,
        \Amasty\AdvancedReview\Model\ImageUploader $imageUploader,
        \Amasty\AdvancedReview\Model\ImagesFactory $imagesFactory,
        \Amasty\AdvancedReview\Model\Repository\ImagesRepository $imagesRepository,
        ReviewResource $reviewResource
    ) {
        $this->reviewFactory = $reviewFactory;
        $this->productRepository = $productRepository;
        $this->ratingFactory = $ratingFactory;
        $this->jsonSerializer = $jsonSerializer;
        $this->configHelper = $configHelper;
        $this->logger = $logger;
        $this->imageUploader = $imageUploader;
        $this->imagesFactory = $imagesFactory;
        $this->imagesRepository = $imagesRepository;
        $this->reviewResource = $reviewResource;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return Value|mixed|void
     * @throws LocalizedException
     */
    public function resolve(Field $field, $context, ResolveInfo $info, array $value = null, array $args = null)
    {
        $inputData = $args['input'];

        if ($product = $this->loadProduct(
            (int) $inputData['product_id'],
            (int) $context->getExtensionAttributes()->getStore()->getWebsiteId()
        )) {
            try {
                $review = $this->createReview($product, $this->prepareReviewData($inputData, $context));
                $this->saveImages((int)$review->getReviewId(), $inputData);
            } catch (Exception $e) {
                $this->logger->error($e->getMessage());
                throw new LocalizedException(__($e->getMessage()));
            }
        }

        return ['success' => true];
    }

    private function saveImages(int $reviewId, array $inputData)
    {
        $images = $inputData['tmp_images_path'] ?? [];
        foreach ($images as $tmpImagePath) {
            $imagePath = $this->imageUploader->copy($tmpImagePath);
            if ($imagePath) {
                /** @var \Amasty\AdvancedReview\Model\Images $model */
                $model = $this->imagesFactory->create();
                $model->setReviewId($reviewId);
                $model->setPath($imagePath);

                $this->imagesRepository->save($model);
            }
        }
    }

    private function prepareReviewData(array $inputData, ContextInterface $context): array
    {
        unset($inputData['review_id']);
        unset($inputData['product_id']);

        $inputData['customer_id'] = $context->getUserId() ?: null;
        $inputData['store_id'] = $context->getExtensionAttributes()->getStore()->getId();
        $inputData['stores'] = [$context->getExtensionAttributes()->getStore()->getId()];

        if (!$this->configHelper->isProsConsEnabled()) {
            unset($inputData['like_about']);
            unset($inputData['not_like_about']);
        }
        if (!$this->configHelper->isRecommendFieldEnabled()) {
            unset($inputData['is_recommended']);
        }
        if ($context->getExtensionAttributes()->getIsCustomer() || !$this->configHelper->isGDPREnabled()) {
            unset($inputData['gdpr']);
        } elseif (!isset($inputData['gdpr'])) {
            throw new GraphQlInputException(__(ValuesOfCorrectType::requiredFieldMessage(
                'AddAdvReviewInput',
                'gdpr',
                'Boolean'
            )));
        }

        return $inputData;
    }

    private function createReview(ProductInterface $product, array $reviewData): Review
    {
        $review = $this->reviewFactory->create();

        $review->setData($reviewData);
        $review->setEntityId($review->getEntityIdByCode(Review::ENTITY_PRODUCT_CODE));
        $review->setEntityPkValue($product->getId());
        $review->setStatusId(Review::STATUS_PENDING);
        $review->save();

        $this->saveAdditionalInfo((int) $review->getId(), $reviewData);

        $this->createRating(
            $review,
            $reviewData['ratings']
        );

        $review->aggregate();

        return $review;
    }

    private function saveAdditionalInfo(int $reviewId, array $reviewData): void
    {
        $data = [];

        foreach (['like_about', 'not_like_about', 'guest_email'] as $item) {
            if (isset($reviewData[$item])) {
                $data[$item] = $reviewData[$item];
            }
        }

        if ($data) {
            $this->reviewResource->insertAdditionalData($reviewId, $data);
        }
    }

    private function createRating(Review $review, string $ratings): void
    {
        $ratings = $this->jsonSerializer->unserialize($ratings);

        foreach ($ratings as $ratingId => $optionId) {
            $rating = $this->ratingFactory->create();

            $rating->setRatingId($ratingId);
            $rating->setReviewId($review->getId());
            $rating->setCustomerId($review->getCustomerId());
            $rating->addOptionVote($optionId, $review->getEntityPkValue());
        }
    }

    private function loadProduct(int $productId, int $currentWebsiteId): ?ProductInterface
    {
        if (!$productId) {
            return null;
        }

        try {
            $product = $this->productRepository->getById($productId);

            if (!in_array($currentWebsiteId, $product->getWebsiteIds())) {
                return null;
            }

            if (!$product->isVisibleInCatalog() || !$product->isVisibleInSiteVisibility()) {
                return null;
            }
        } catch (NoSuchEntityException $noEntityException) {
            return null;
        }

        return $product;
    }
}
