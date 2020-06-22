<?php

namespace Eguana\Magazine\Block;

use Eguana\Magazine\Api\MagazineRepositoryInterface;
use Eguana\Magazine\Helper\Data;
use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Cms\Model\Template\FilterProvider;
use Magento\Directory\Model\CountryFactory;
use Magento\Framework\DataObject\IdentityInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

/**
 * Abstract class for blocks
 *
 * Class AbstractBlock
 */
class AbstractBlock extends Template implements IdentityInterface
{
    /**
     * @var Data
     */
    private $helperData;
    /**
     * @var CollectionFactory
     */
    protected $magazineCollectionFactory;
    /**
     * @var UrlInterface
     */
    private $urlInterface;

    /**
     * @var FilterProvider
     */
    protected $filterProvider;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;

    /**
     * @var CountryFactory
     */
    protected $countryFactory;

    /**
     * @var MagazineRepositoryInterface
     */
    protected $magazineRepository;

    /**
     * Magazine constructor.
     * @param Template\Context $context
     * @param CollectionFactory $collectionFactory
     * @param UrlInterface $urlInterface
     * @param FilterProvider $filterProvider
     * @param StoreManagerInterface $storeManager
     * @param MagazineRepositoryInterface $magazineRepository
     * @param CountryFactory $countryFactory
     * @param Data $helperData
     * @param array $data
     */
    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        UrlInterface $urlInterface,
        FilterProvider $filterProvider,
        StoreManagerInterface $storeManager,
        MagazineRepositoryInterface $magazineRepository,
        CountryFactory $countryFactory,
        Data $helperData,
        array $data = []
    ) {
        $this->magazineCollectionFactory = $collectionFactory;
        $this->urlInterface = $urlInterface;
        $this->filterProvider = $filterProvider;
        $this->storeManager = $storeManager;
        $this->magazineRepository = $magazineRepository;
        $this->countryFactory = $countryFactory;
        $this->helperData= $helperData;

        parent::__construct($context, $data);
    }

    /**
     * @return array|string[]
     */
    public function getIdentities()
    {
        return [\Eguana\Magazine\Model\Magazine::CACHE_TAG];
    }

    /**
     * @return int
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getStoreId()
    {
        return $this->_storeManager->getStore()->getId();
    }

    /**
     * Get magazine collection
     *
     * @return mixed
     */
    public function getCollection($type)
    {
        if (!$this->getData('collection')) {
            /** @var \Eguana\Magazine\Model\ResourceModel\Magazine\Collection $magazineCollection */
            $magazineCollection = $this->magazineCollectionFactory->create();
            $storeId = $this->storeManager->getStore()->getId();
            $sortDirection = 'asc';
            if ($this->helperData->getConfig('magazine/general/sort_direction') == 1) {
                $sortDirection = 'desc';
            }
            $magazineCollection->addFieldToFilter("is_active", ["eq" => true])
                ->addFieldToFilter('store_id', ['in' => [0, (int)$storeId]])
                ->setOrder(
                    "sort_order",
                    $sortDirection
                )
                ->addFieldToFilter('type', ['eq' => $type]);

        }
        return $magazineCollection;
//        return $this->getData('collection');
    }

    /**
     * To filter the content
     * This function will get the content, specially the page builder content and make it renderable at frontend.
     * @param $content
     * @return mixed
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */

    public function contentFiltering($content)
    {
        $storeId = $this->storeManager->getStore()->getId();
        return $this->filterProvider->getBlockFilter()->setStoreId($storeId)
            ->filter($content);
    }

    /**
     * To get relative url
     * This function will return the full relative url for magazine
     * @param $urlkey
     * @return string
     */
    public function getMagazineUrl($id)
    {
        return $this->getUrl('magazine/details/index', ['id' => $id]);
    }

    public function getDetail($id)
    {
       return $this->magazineRepository->getById($id);
    }

    public function videoThumbnail()
    {
        $magazineCollection = $this->magazineCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();
        $sortDirection = 'asc';
        $magazineCollection->addFieldToFilter("is_active", ["eq" => true])
            ->addFieldToFilter('store_id', ['in' => [0, (int)$storeId]])
            ->setOrder(
                "sort_order",
                $sortDirection
            )
            ->addFieldToFilter('type', ['eq' => 3])
        ->setPageSize(1);
        return $magazineCollection;
    }
}
