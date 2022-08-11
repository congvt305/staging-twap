<?php
declare(strict_types=1);

namespace CJ\ReviewsImportExport\Block;

use Amasty\AdvancedReview\Model\Sources\Recommend;
use Amasty\AdvancedReview\Model\Toolbar\UrlBuilder;
use Magento\Framework\View\Element\Template;
use Magento\Review\Model\ResourceModel\Review\Collection as ReviewCollection;

class Summary extends \Amasty\AdvancedReview\Block\Summary
{
    /**
     * @var null|int
     */
    private $votedRecommendCount = null;

    /**
     * @var \Amasty\AdvancedReview\Helper\Config
     */
    private $configHelper;

    /**
     * @var \Magento\Review\Model\ResourceModel\Review\CollectionFactory
     */
    private $reviewsColFactory;

    /**
     * @param Template\Context $context
     * @param \Magento\Review\Model\Review\SummaryFactory $summaryModFactory
     * @param \Magento\Review\Model\RatingFactory $ratingFactory
     * @param \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory
     * @param \Amasty\AdvancedReview\Helper\Config $configHelper
     * @param \Amasty\AdvancedReview\Helper\BlockHelper $advancedHelper
     * @param \Magento\Framework\DataObjectFactory $dataObjectFactory
     * @param UrlBuilder $urlBuilder
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        \Magento\Review\Model\Review\SummaryFactory $summaryModFactory,
        \Magento\Review\Model\RatingFactory $ratingFactory,
        \Magento\Review\Model\ResourceModel\Review\CollectionFactory $collectionFactory,
        \Amasty\AdvancedReview\Helper\Config $configHelper,
        \Amasty\AdvancedReview\Helper\BlockHelper $advancedHelper,
        \Magento\Framework\DataObjectFactory $dataObjectFactory,
        UrlBuilder $urlBuilder,
        array $data = []
    ) {
        $this->configHelper = $configHelper;
        $this->reviewsColFactory = $collectionFactory;
        parent::__construct(
            $context,
            $summaryModFactory,
            $ratingFactory,
            $collectionFactory,
            $configHelper,
            $advancedHelper,
            $dataObjectFactory,
            $urlBuilder,
            $data
        );
    }


    /**
     * Should show recommended
     *
     * @return bool
     */
    public function shouldShowRecommended()
    {
        $result = $this->configHelper->isRecommendFieldEnabled()
            && $this->getRecommendedVotedCount();

        return $result;
    }

    /**
     * Get recommend percent
     *
     * @return int
     */
    public function getRecomendedPercent()
    {
        $collection = $this->getRecommendedCollection()
            ->addFieldToFilter('is_recommended', Recommend::RECOMMENDED);

        $result = 0;
        $totalCount = $this->getRecommendedVotedCount();
        if ($totalCount) {
            $result = round($collection->getSize() / $totalCount * 100);
        }

        return $result;
    }

    /**
     * Get recommend voted count
     *
     * @return int|null
     */
    private function getRecommendedVotedCount()
    {
        if ($this->votedRecommendCount == null) {
            $this->votedRecommendCount = $this->getRecommendedCollection()->getSize();
        }

        return $this->votedRecommendCount;
    }


    /**
     * Get recommend collection
     *
     * @return ReviewCollection
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    private function getRecommendedCollection()
    {
        $collection = $this->reviewsColFactory->create()->addStoreFilter(
            $this->_storeManager->getStore()->getId()
        )->addStatusFilter(
            \Magento\Review\Model\Review::STATUS_APPROVED
        )->addEntityFilter(
            'product',
            $this->getProduct()->getId()
        )->addFieldToFilter(
            'main_table.entity_pk_value',
            $this->getProduct()->getId()
        );

        return $collection;
    }
}
