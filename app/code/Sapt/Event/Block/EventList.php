<?php

namespace Sapt\Event\Block;


use Eguana\EventManager\Api\EventManagerRepositoryInterface;
use Eguana\EventManager\Model\EventManagerConfiguration\EventManagerConfiguration;
use Eguana\EventManager\Model\ResourceModel\EventManager\Collection;
use Eguana\EventManager\Model\ResourceModel\EventManager\CollectionFactory;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Template;
use Magento\Store\Model\StoreManagerInterface;

class EventList extends \Magento\Framework\View\Element\Template
{
    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var CollectionFactory
     */
    private $eventManagerCollectionFactory;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var EventManagerRepositoryInterface
     */
    private $eventManagerRepository;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    public function __construct(
        Template\Context $context,
        CollectionFactory $collectionFactory,
        EventManagerRepositoryInterface $eventManagerRepository,
        StoreManagerInterface $storeManager,
        TimezoneInterface $timezone,
        array $data = []
    ) {
        $this->eventManagerCollectionFactory = $collectionFactory;
        $this->storeManager = $storeManager;
        $this->eventManagerRepository = $eventManagerRepository;
        $this->timezone = $timezone;
        parent::__construct($context, $data);
    }

    /**
     * get store id
     * @return int
     */
    public function getStoreId()
    {
        try {
            return $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
        }
    }

    /**
     * @return Collection
     * @throws NoSuchEntityException
     */
    public function getEventCollection()
    {
        $currentDate = $this->timezone->date()->format(self::DATE_FORMAT);
        $sortOrder = 'desc';
        $count = 3;
        $eventManagerCollection = $this->eventManagerCollectionFactory->create();
        $storeId = $this->storeManager->getStore()->getId();

        $eventManagerCollection->addFieldToFilter(
            "is_active",
            ["eq" => true]
        )->addStoreFilter($storeId)->addFieldToFilter(
            "end_date",
            ['gteq' => $currentDate]
        )->addFieldToFilter(
            "start_date",
            ['lt' => $currentDate]
        )->setOrder(
            "entity_id",
            $sortOrder
        );
        $eventManagerCollection->setPageSize($count);
        return $eventManagerCollection;
    }

    /**
     * To get relative url
     * This function will return the full relative url for EventManager
     * @param $urlKey
     * @return string
     */
    public function getEventManagerUrl($urlKey)
    {
        return $this->getUrl('events/detail/index', ['id' => $urlKey]);
    }

    /**
     * It will return the thumbanil image URL
     *
     * @param $file
     * @return string
     */
    public function getThumbnailImageURL($file)
    {
        if ($file == '') {
            return '';
        }
        return $this->getMediaUrl($file);
    }

    /**
     * Get file url
     * @param $file
     * @return string
     */
    public function getMediaUrl($file)
    {
        try {
            $file = ltrim(str_replace('\\', '/', $file), '/');
            return $this->storeManager
                    ->getStore()
                    ->getBaseUrl(UrlInterface::URL_TYPE_MEDIA) . $file;
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    public function changeDateFormat($date)
    {
        return $this->timezone->date($date)->format(self::DATE_FORMAT);
    }
}
