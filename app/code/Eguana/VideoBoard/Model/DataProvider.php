<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/6/20
 * Time: 7:29 PM
 */

namespace Eguana\VideoBoard\Model;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\CollectionFactory;
use Eguana\VideoBoard\Model\ResourceModel\VideoBoard\Collection;

/**
 * This class is used to get the Data
 *
 * Class DataProvider
 * Eguana\VideoBoard\Model
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
     * @param CollectionFactory $videoBoardCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $videoBoardCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $videoBoardCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManagerInterface;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
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
        /** @var Customer $video */
        foreach ($items as $video) {
            $thumbnailImagePath = explode('/', $video->getData('thumbnail_image'));
            $thumbnailImageCount = count($thumbnailImagePath);
            if ($thumbnailImageCount == 2) {
                $thumbnailImage = [
                    'url' => $this->storeManager->getStore()
                            ->getBaseUrl('media') . $video->getData('thumbnail_image'),
                    'file' => $thumbnailImagePath[$thumbnailImageCount - 1]
                ];
                $video->setData('thumbnail_image', [$thumbnailImage]);
            }
            $this->loadedData[$video->getId()] = $video->getData();
        }
        $data = $this->dataPersistor->get('eguana_video_board');
        if (!empty($data)) {
            $video = $this->collection->getNewEmptyItem();
            $video->setData($data);
            $this->loadedData[$video->getId()] = $video->getData();
            $this->dataPersistor->clear('eguana_video_board');
        }
        return $this->loadedData;
    }
}
