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

use Eguana\Magazine\Model\ResourceModel\Magazine\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    /**
     * @var \Eguana\Magazine\Model\ResourceModel\Magazine\Collection
     */
    protected $collection;

    /**
     * @var DataPersistorInterface
     */
    protected $dataPersistor;

    /**
     * @var array
     */
    protected $loadedData;

    /**
     * @var StoreManagerInterface
     */
    protected $storeManager;
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
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $magazineCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $magazineCollectionFactory->create();
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
        /** @var \Eguana\Magazine\Model\Magazine $magazine */
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

        return $this->loadedData;
    }
}
