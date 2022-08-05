<?php

namespace CJ\AmastyReview\Block\Widget;

use Amasty\Meta\Model\System\Store;
use Magento\Framework\View\Element\Template;
use Magento\Setup\Exception;
use Magento\Widget\Block\BlockInterface;
use CJ\AmastyReview\Model\ResourceModel\Images\CollectionFactory;
use Magento\Catalog\Model\ProductRepository;
use Magento\Review\Model\ResourceModel\Review\Summary\CollectionFactory as SummaryCollectionFactory;
use Amasty\AdvancedReview\Helper\ImageHelper;

/**
 * Class ReviewAll
 * @package CJ\AmastyReview\Block\Widget
 */
class ReviewAll extends Template implements BlockInterface {

    /**
     * @var string
     */
    protected $_template = "widget/reviewAll.phtml";

    /**
     * @var CollectionFactory
     */
    private $_collectionFactory;

    /**
     * @var ProductRepository
     */
    private $_productRepository;

    /**
     * @var SummaryCollectionFactory
     */
    private $_summaryCollectionFactory;

    /**
     * @var ImageHelper
     */
    private $_imageHelper;

    /**
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param ProductRepository $productRepository
     * @param SummaryCollectionFactory $summaryCollectionFactory
     * @param ImageHelper $imageHelper
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        ProductRepository $productRepository,
        SummaryCollectionFactory $summaryCollectionFactory,
        ImageHelper $imageHelper,
        array $data = []
    ) {
        $this->_collectionFactory        = $collectionFactory;
        $this->_productRepository        = $productRepository;
        $this->_summaryCollectionFactory = $summaryCollectionFactory;
        $this->_imageHelper              = $imageHelper;
        parent::__construct($context, $data);
    }

    /**
     * @return false|string
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getReviewAll() {
        $collection = $this->_collectionFactory->create();
        $dataImage  = $collection->getReviewData();

        $result     = [];
        $store      = $this->_storeManager->getStore();
        $storeUrl   = $store->getBaseUrl(\Magento\Framework\UrlInterface::URL_TYPE_WEB);

        foreach ($dataImage->getItems() as $item) {
            try {
                $productId         = $item->getProductId();
                $image             = $this->_imageHelper->resize(substr($item->getPhotoUrl(), 1), 200);
                $url               = $storeUrl . $this->_productRepository->getById($productId)->getUrlKey() . '.html';
                $summaryCollection = $this->_summaryCollectionFactory->create();
                $summaryData       = $summaryCollection->addEntityFilter($productId)
                    ->addStoreFilter($store->getId())->getFirstItem();
            } catch (\Exception $exception) {
                continue;
            }

            $result[] = [
                'email'      => $item->getEmail(),
                'link'       => $url,
                'created_at' => $item->getCreatedAt(),
                'photo_url'  => $image,
                'content'    => $item->getContent(),
                'titlte'     => $item->getTitle(),
                'rating'     => $summaryData->getRatingSummary() / 20,
                'like_cnt'   => $item->getLikeCnt(),
                'hate_cnt'   => $item->getHateCnt()
            ];
        }

        return json_encode($result);
    }
}
