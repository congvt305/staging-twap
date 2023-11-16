<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Query;

use Amasty\AdvancedReview\Api\CommentRepositoryInterface;
use Amasty\AdvancedReview\Model\Repository\VoteRepository;
use Amasty\AdvancedReview\Model\ResourceModel\Review\Collection as ReviewCollection;
use Amasty\AdvancedReview\Model\ResourceModel\Review\CollectionFactory;
use Amasty\AdvancedReview\Model\Toolbar\Applier;
use Amasty\AdvancedReviewGraphQl\Model\Toolbar\UrlBuilder;
use Magento\Framework\GraphQl\Exception\GraphQlNoSuchEntityException;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Amasty\AdvancedReview\Block\Summary;
use Magento\Catalog\Model\ProductRepository;
use Amasty\AdvancedReview\Helper\Config;
use Magento\Review\Model\ResourceModel\Rating\Option\Vote\CollectionFactory as VoteCollectionFactory;
use Amasty\AdvancedReview\Block\Images;
use Magento\Store\Model\StoreManagerInterface;

class Review implements ResolverInterface
{
    /**
     * @var Summary
     */
    private $summary;

    /**
     * @var ProductRepository
     */
    private $productRepository;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var VoteCollectionFactory
     */
    private $voteFactory;

    /**
     * @var Images
     */
    private $images;

    /**
     * @var CommentRepositoryInterface
     */
    private $commentRepository;

    /**
     * @var Applier
     */
    private $applier;

    /**
     * @var UrlBuilder
     */
    private $urlBuilder;

    /**
     * @var VoteRepository
     */
    private $voteRepository;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        Summary $summary,
        ProductRepository $productRepository,
        CollectionFactory $collectionFactory,
        Config $config,
        VoteRepository $voteRepository,
        VoteCollectionFactory $voteFactory,
        Images $images,
        CommentRepositoryInterface $commentRepository,
        Applier $applier,
        UrlBuilder $urlBuilder,
        StoreManagerInterface $storeManager
    ) {
        $this->summary = $summary;
        $this->productRepository = $productRepository;
        $this->collectionFactory = $collectionFactory;
        $this->config = $config;
        $this->voteFactory = $voteFactory;
        $this->images = $images;
        $this->commentRepository = $commentRepository;
        $this->applier = $applier;
        $this->urlBuilder = $urlBuilder;
        $this->voteRepository = $voteRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param Field $field
     * @param \Magento\Framework\GraphQl\Query\Resolver\ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     * @throws \Exception
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        $this->urlBuilder->setParams($args);
        try {
            $product = $this->productRepository->getById($args['productId']);
            $reviewCollection = $this->getFilteredReviewCollection(
                (int) $product->getId(),
                (int) $context->getExtensionAttributes()->getStore()->getId(),
                $args['page']
            );
        } catch (\Exception $e) {
            throw new GraphQlNoSuchEntityException(__('Wrong parameter storeId.'));
        }

        $this->summary->setProduct($product);
        $this->prepareAdditionalData($reviewCollection, $context->getExtensionAttributes()->getStore()->getId());
        $data = $reviewCollection->toArray();
        $data['totalRecordsFiltered'] = $data['totalRecords'];
        $data['totalRecords'] = $this->getReviewCollection(
            (int) $product->getId(),
            (int) $context->getExtensionAttributes()->getStore()->getId()
        )->getSize();
        $this->summary->setDisplayedCollection($reviewCollection);

        $extraData = [
            'ratingSummary' => $this->summary->getRatingSummary(),
            'ratingSummaryValue' =>  $this->summary->getRatingSummaryValue(),
            'detailedSummary' => $this->prepareDetailedSummary(),
            'recomendedPercent' => $this->summary->getRecomendedPercent()
        ];

        return array_merge($data, $extraData);
    }

    private function getFilteredReviewCollection(int $productId, int $storeId, int $page): ReviewCollection
    {
        $reviewCollection = $this->getReviewCollection($productId, $storeId);
        $reviewCollection->setCurPage($page);
        $reviewCollection->setPageSize($this->config->getReviewsPerPage());
        $reviewCollection->addFieldToSelect([
            new \Zend_Db_Expr('detail.like_about'),
            new \Zend_Db_Expr('detail.not_like_about'),
            new \Zend_Db_Expr('detail.guest_email'),
            new \Zend_Db_Expr('main_table.*')
        ]);
        $reviewCollection->setDateOrder();

        $reviewCollection->setFlag(ReviewCollection::STRICT_COUNT_FLAG, true);

        $this->applier->execute($reviewCollection);

        return $reviewCollection;
    }

    private function getReviewCollection(int $productId, int $storeId): ReviewCollection
    {
        return $this->collectionFactory->create()->addStoreFilter($storeId)
            ->addStatusFilter(\Magento\Review\Model\Review::STATUS_APPROVED)
            ->addEntityFilter('product', $productId);
    }

    /**
     * @return array
     */
    private function prepareDetailedSummary()
    {
        $detailedSummary = $this->summary->getDetailedSummary();

        return [
          'one' => $detailedSummary[1],
          'two' => $detailedSummary[2],
          'three' => $detailedSummary[3],
          'four' => $detailedSummary[4],
          'five' => $detailedSummary[5],
        ];
    }

    /**
     * @param $reviewCollection
     * @param $storeId
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareAdditionalData($reviewCollection, $storeId)
    {
        foreach ($reviewCollection as $review) {
            $this->prepareVotes($review, $storeId);
            $this->prepareImages($review);
            $review->setComments($this->commentRepository->getListByReviewId($review->getId())->getItems());
        }
    }

    /**
     * @param $review
     * @param $storeId
     */
    private function prepareVotes($review, $storeId)
    {
        $votes = $this->voteRepository->getVotesCount($review->getId());
        $review->setData('plus_review', $votes['plus'] ?? 0);
        $review->setData('minus_review', $votes['minus'] ?? 0);
        $votes = $this->voteFactory->create()->setReviewFilter($review->getId())
            ->setStoreFilter($storeId)
            ->addRatingInfo($storeId);
        $review->setRatingVotes($votes->getItems());
    }

    /**
     * @param $review
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function prepareImages($review)
    {
        $images = [];
        $this->images->setReviewId($review->getId());
        $baseUrl = $this->storeManager->getStore()->getBaseUrl();
        foreach ($this->images->getCollection() as $image) {
            $images[] = [
                'full_path' => $this->getRelativePath($this->images->getFullImagePath($image), $baseUrl),
                'resized_path' => $this->getRelativePath($this->images->getResizedImagePath($image), $baseUrl),
            ];
        }

        $review->setImages($images);
    }

    private function getRelativePath(string $path, string $baseUrl)
    {
        return str_replace($baseUrl, '', $path);
    }
}
