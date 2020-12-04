<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Bilal
 * Date: 10/11/20
 * Time: 4:30 PM
 */
namespace Eguana\NewsBoard\Ui\DataProvider\Form;

use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Eguana\NewsBoard\Model\ResourceModel\News\CollectionFactory;
use Eguana\NewsBoard\Model\ResourceModel\News\Collection;

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
        try {
            if (isset($this->loadedData)) {
                return $this->loadedData;
            }
            $items = $this->collection->getItems();
            /** @var $news */
            foreach ($items as $news) {
                $thumbnailImagePath = explode('/', $news->getData('thumbnail_image'));
                $thumbnailImageCount = count($thumbnailImagePath);
                if ($thumbnailImageCount == 2) {
                    $thumbnailImage = [
                        'url' => $this->storeManager->getStore()
                                ->getBaseUrl('media') . $news->getData('thumbnail_image'),
                        'file' => $thumbnailImagePath[$thumbnailImageCount - 1]
                    ];
                    $news->setData('thumbnail_image', [$thumbnailImage]);
                }
                $this->loadedData[$news->getId()] = $news->getData();
            }
            $data = $this->dataPersistor->get('news_add_form');
            if (!empty($data)) {
                $news = $this->collection->getNewEmptyItem();
                $news->setData($data);
                $this->loadedData[$news->getId()] = $news->getData();
                $this->dataPersistor->clear('news_add_form');
            }
        } catch (\Exception $e) {
            $e->getMessage();
        }
        return $this->loadedData;
    }
}
