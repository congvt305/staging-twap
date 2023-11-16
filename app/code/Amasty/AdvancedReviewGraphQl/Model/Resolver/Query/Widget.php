<?php

declare(strict_types=1);

namespace Amasty\AdvancedReviewGraphQl\Model\Resolver\Query;

use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\GraphQl\Query\Resolver\ContextInterface;
use Magento\Framework\GraphQl\Schema\Type\ResolveInfo;
use Magento\Framework\GraphQl\Config\Element\Field;
use Magento\Framework\GraphQl\Query\ResolverInterface;
use Magento\Review\Model\Review;

class Widget implements ResolverInterface
{
    /**
     * @var \Amasty\AdvancedReview\Block\Widget\Reviews
     */
    private $reviewsWidget;

    /**
     * @var \Amasty\Base\Model\Serializer
     */
    private $serializer;

    /**
     * @var \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection
     */
    private $widgetCollection;

    /**
     * @var \Magento\Catalog\Helper\Product\Compare
     */
    private $compareHelper;

    public function __construct(
        \Amasty\AdvancedReview\Block\Widget\Reviews $reviewsWidget,
        \Amasty\Base\Model\Serializer $serializer,
        \Magento\Catalog\Helper\Product\Compare $compareHelper,
        \Magento\Widget\Model\ResourceModel\Widget\Instance\Collection $widgetCollection
    ) {
        $this->reviewsWidget = $reviewsWidget;
        $this->widgetCollection = $widgetCollection;
        $this->serializer = $serializer;
        $this->compareHelper = $compareHelper;
    }

    /**
     * @param Field $field
     * @param ContextInterface $context
     * @param ResolveInfo $info
     * @param array|null $value
     * @param array|null $args
     * @return array|\Magento\Framework\GraphQl\Query\Resolver\Value|mixed
     */
    public function resolve(
        Field $field,
        $context,
        ResolveInfo $info,
        array $value = null,
        array $args = null
    ) {
        try {
            $categoryId = isset($args['categoryId']) ? (int) $args['categoryId'] : null;
            $productId = isset($args['productId']) ? (int) $args['productId'] : null;
            $this->setWidgetData(
                $args['widgetId'],
                (int) $context->getExtensionAttributes()->getStore()->getId(),
                $categoryId,
                $productId
            );
        } catch (\Exception $e) {
            $data['title'] = $e->getMessage();
            return $data;
        }

        $data = $this->reviewsWidget->getData();
        $reviews = $this->reviewsWidget->getReviewsCollection()->getItems();
        foreach ($reviews as $review) {
            $data['items'][] = $this->prepareData($review, $context);
        }
        $data['reviews_count'] = count($reviews);

        return $data;
    }

    /**
     * @param int $id
     * @param int $storeId
     * @param int|null $categoryId
     * @param int|null $productId
     * @throws LocalizedException
     */
    private function setWidgetData(int $id, int $storeId, ?int $categoryId, ?int $productId): void
    {
        $widget = $this->widgetCollection->getItemById($id);
        if (!$this->validateStore($widget, $storeId)) {
            throw new LocalizedException(__('Wrong parameter storeId.'));
        }
        $widgetParams = $widget->getWidgetParameters();

        $this->reviewsWidget->setNameInLayout('advanced_review_widget');
        $this->reviewsWidget->setCategoryId($categoryId);
        $this->reviewsWidget->setProductId($productId);
        $this->reviewsWidget->setData('store_id', $storeId);
        $this->reviewsWidget->setData($widgetParams);
        $this->reviewsWidget->setData(
            'conditions',
            $this->serializer->serialize($this->reviewsWidget->getConditions())
        );
    }

    /**
     * @param $widget
     * @param int $storeId
     * @return bool
     */
    private function validateStore($widget, int $storeId)
    {
        return in_array(\Magento\Store\Model\Store::DEFAULT_STORE_ID, $widget->getStoreIds())
            || in_array($storeId, $widget->getStoreIds());
    }

    private function prepareData(Review $review, ContextInterface $context): array
    {
        $product = $this->reviewsWidget->getProduct($review);
        $ratingsVotes = $review->getRatingVotes();
        $format = $this->reviewsWidget->getDateFormat() ? : \IntlDateFormatter::MEDIUM;

        $data['model'] = $product;
        $data['productUrl'] = $this->getRelativePath($product->getProductUrl(), $context);
        $data['name'] = $product->getName();
        foreach ($ratingsVotes as $ratingsVote) {
            $data['rating_votes'][] = $ratingsVote->getData();
        }
        $data['recommendedHtml'] = $this->reviewsWidget->getAdvancedHelper()->getRecommendedHtml($review);
        $data['reviewBy'] = $review->getNickname();
        $data['reviewMessage'] = $this->reviewsWidget->getReviewMessage($review->getDetail());
        $data['date'] = $this->reviewsWidget->formatDate($review->getCreatedAt(), $format);

        return $data;
    }

    protected function getRelativePath(string $url, ContextInterface $context): string
    {
        $baseUrl = trim($context->getExtensionAttributes()->getStore()->getBaseUrl(), '/');

        return str_replace($baseUrl, '', $url);
    }
}
