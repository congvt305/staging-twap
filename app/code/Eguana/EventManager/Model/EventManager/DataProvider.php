<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 29/6/20
 * Time: 4:30 PM
 */
namespace Eguana\EventManager\Model\EventManager;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\EventManager\Model\ResourceModel\EventManager\CollectionFactory;
use Eguana\EventManager\Model\ResourceModel\EventManager\Collection;

/**
 * This class is used to get the Data
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * Constructor
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $eventManagerCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $eventManagerCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $eventManagerCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManagerInterface;
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $event */
        foreach ($items as $event) {
            $thumbnailImagePath = explode('/', $event->getData('thumbnail_image'));
            $thumbnailImageCount = count($thumbnailImagePath);
            if ($thumbnailImageCount == 2) {
                $thumbnailImage = [
                    'url' => $this->storeManager->getStore()
                            ->getBaseUrl('media') . $event->getData('thumbnail_image'),
                    'file' => $thumbnailImagePath[$thumbnailImageCount - 1]
                ];
                $event->setData('thumbnail_image', [$thumbnailImage]);
            }
            $this->loadedData[$event->getId()] = $event->getData();
        }
        $data = $this->dataPersistor->get('eguana_event_manager');
        if (!empty($data)) {
            $event = $this->collection->getNewEmptyItem();
            $event->setData($data);
            $this->loadedData[$event->getId()] = $event->getData();
            $this->dataPersistor->clear('eguana_event_manager');
        }
        return $this->loadedData;
    }
}
