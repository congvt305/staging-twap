<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/17/20
 * Time: 5:08 AM
 */
namespace Eguana\Magazine\Model\Magazine;

use Eguana\Magazine\Model\Magazine as MagazineAlias;
use Eguana\Magazine\Model\ResourceModel\Magazine\Collection as CollectionAlias;
use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider as AbstractDataProviderAlias;
use Psr\Log\LoggerInterface;

/**
 * This class is used for data provider
 * Class DataProvider
 */
class DataProvider extends AbstractDataProviderAlias
{
    /**
     * @var CollectionAlias
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
     * @var LoggerInterface;
     */
    private $logger;

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
     * @param CollectionFactory $magazineCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManagerInterface
     * @param array $meta
     * @param array $data
     * @param LoggerInterface $logger

     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $magazineCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        LoggerInterface $logger,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $magazineCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManagerInterface;
        $this->logger = $logger;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    /**
     * This function is used for get data
     * @return array
     */
    public function getData()
    {
        try {
            if (isset($this->loadedData)) {
                return $this->loadedData;
            }
            $items = $this->collection->getItems();
            /** @var MagazineAlias $magazine */
            foreach ($items as $magazine) {
                $thumbnailImagePath = explode('/', $magazine->getData('thumbnail_image'));

                $thumbnailImageCount = count($thumbnailImagePath);

                if ($thumbnailImageCount == 2) {
                    $thumbnailImage = [
                        'url' => $this->storeManager->getStore()
                                ->getBaseUrl('media') . $magazine->getData('thumbnail_image'),
                        'file' => $thumbnailImagePath[$thumbnailImageCount - 1]
                    ];
                    $magazine->setData('thumbnail_image', [$thumbnailImage]);
                }

                $this->loadedData[$magazine->getId()] = $magazine->getData();
            }

            $data = $this->dataPersistor->get('eguana_magazine');
            if (!empty($data)) {
                $magazine = $this->collection->getNewEmptyItem();
                $magazine->setData($data);
                $this->loadedData[$magazine->getId()] = $magazine->getData();
                $this->dataPersistor->clear('eguana_magazine');
            }
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $this->loadedData;
    }
}
