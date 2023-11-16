<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Query;

use Amasty\AdvancedReview\Block\Review\Toolbar;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Review\Model\Rating;
use Magento\Review\Model\ResourceModel\Rating\Collection as RatingCollection;
use Magento\Review\Model\ResourceModel\Rating\CollectionFactory as RatingCollectionFactory;

class Setting implements ResolverInterface
{
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var \Amasty\AdvancedReview\Helper\Config
     */
    private $settings;

    /**
     * @var RatingCollectionFactory
     */
    private $ratingCollectionFactory;

    /**
     * @var Toolbar
     */
    private $toolbar;

    public function __construct(
        \Amasty\AdvancedReview\Helper\Config $settings,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        RatingCollectionFactory $ratingCollectionFactory,
        Toolbar $toolbar
    ) {
        $this->storeManager = $storeManager;
        $this->settings = $settings;
        $this->ratingCollectionFactory = $ratingCollectionFactory;
        $this->toolbar = $toolbar;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
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
        try {
            return $this->getData($context);
        } catch (\Exception $e) {
            return ['error' => __('Wrong post id.')];
        }
    }

    private function getData(ContextInterface $context): array
    {
        return [
            'isGDPREnabled' => $this->settings->isGDPREnabled(),
            'getGDPRText' => $this->settings->getGDPRText(),
            'getReviewImageWidth' => $this->settings->getReviewImageWidth(),
            'isAllowReminder' => $this->settings->isReminderEnabled(),
            'isCommentsEnabled' => $this->settings->isCommentsEnabled(),
            'isGuestCanComment' => $this->settings->isGuestCanComment(),
            'isReminderEnabled' => $this->settings->isReminderEnabled(),
            'isRecommendFieldEnabled' => $this->settings->isRecommendFieldEnabled(),
            'isAllowGuest' => $this->settings->isAllowGuest(),
            'isAllowAnswer' => $this->settings->isAllowAnswer(),
            'isAllowCoupons' => $this->settings->isAllowCoupons(),
            'isAllowHelpful' => $this->settings->isAllowHelpful(),
            'isAllowImages' => $this->settings->isAllowImages(),
            'isProsConsEnabled' => $this->settings->isProsConsEnabled(),
            'availableOrders' => $this->prepareAvailableToolbarOptions($this->toolbar->getAvailableOrders()),
            'availableFilters' => $this->prepareAvailableToolbarOptions($this->toolbar->getAvailableFilters()),
            'isFilteringEnabled' => $this->toolbar->isFilteringEnabled(),
            'isSortingEnabled' => $this->toolbar->isSortingEnabled(),
            'isToolbarDisplayed' => $this->toolbar->isToolbarDisplayed(),
            'perPage' => $this->settings->getReviewsPerPage(),
            'ratings' => $this->getRatings((int) $context->getExtensionAttributes()->getStore()->getId()),
            'isGuestEmailShow' => $this->settings->isEmailFieldEnable(),
            'isImagesRequired' => $this->settings->isImagesRequired(),
            'slidesToShow' => $this->settings->getSlidesToShow()
        ];
    }

    private function prepareAvailableToolbarOptions(array $data): array
    {
        $items = [];
        foreach ($data as $code => $item) {
            $items[] = [
                'code' => $code,
                'label' => $item->getText()
            ];
        }

        return $items;
    }

    private function getRatings(int $storeId): array
    {
        /** @var RatingCollection $ratingCollection */
        $ratingCollection = $this->ratingCollectionFactory->create();

        $ratingCollection->addEntityFilter('product');
        $ratingCollection->setPositionOrder();
        $ratingCollection->addRatingPerStoreName($storeId);
        $ratingCollection->setStoreFilter($storeId);
        $ratingCollection->setActiveFilter(true);

        $ratings = [];
        /** @var Rating $rating */
        foreach ($ratingCollection as $rating) {
            $ratings[] = [
                'rating_id' => $rating->getId(),
                'rating_code' => $rating->getRatingCode(),
                'rating_options' => $rating->getOptions()
            ];
        }

        return $ratings;
    }
}
