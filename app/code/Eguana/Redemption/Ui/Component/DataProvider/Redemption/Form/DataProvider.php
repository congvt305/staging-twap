<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 15/10/20
 * Time: 4:00 PM
 */
declare(strict_types=1);

namespace Eguana\Redemption\Ui\Component\DataProvider\Redemption\Form;

use Eguana\Redemption\Model\ResourceModel\Redemption\CollectionFactory;
use Magento\Framework\App\Request\DataPersistorInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Catalog\Model\Category\FileInfo;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This class is used to get the Data
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
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
     * @var FileInfo
     */
    private $fileInfo;

    /**
     * DataProvider constructor.
     *
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $redemptionCollectionFactory
     * @param DataPersistorInterface $dataPersistor
     * @param StoreManagerInterface $storeManagerInterface
     * @param FileInfo $fileInfo
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $redemptionCollectionFactory,
        DataPersistorInterface $dataPersistor,
        StoreManagerInterface $storeManagerInterface,
        FileInfo $fileInfo,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $redemptionCollectionFactory->create();
        $this->dataPersistor = $dataPersistor;
        $this->storeManager = $storeManagerInterface;
        $this->fileInfo = $fileInfo;
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
     * @throws NoSuchEntityException
     */
    public function getData()
    {
        if (isset($this->loadedData)) {
            return $this->loadedData;
        }
        $items = $this->collection->getItems();
        /** @var $redemption */
        foreach ($items as $redemption) {
            $thumbnailImagePath = explode('/', $redemption->getData('image'));
            $thumbnailImageCount = count($thumbnailImagePath);
            if ($redemption->getData('image')) {
                $url = $this->storeManager->getStore()
                        ->getBaseUrl('media') . $redemption->getData('image');
                $stat = $this->fileInfo->getStat($url);
                $thumbnailImage = [
                    'url' => $url,
                    'file' => $thumbnailImagePath[$thumbnailImageCount - 1],
                    'size' => isset($stat) ? $stat['size'] : 0
                ];
                $redemption->setData('image', [$thumbnailImage]);
                $redemption->setData('store_id_name', $redemption->getStoreId());
            }
            if ($redemption->getData('thank_you_image')) {
                $url = $this->storeManager->getStore()
                        ->getBaseUrl('media') . $redemption->getData('thank_you_image');
                $stat = $this->fileInfo->getStat($url);
                $thumbnailImage = [
                    'url' => $url,
                    'file' => $thumbnailImagePath[$thumbnailImageCount - 1],
                    'size' => isset($stat) ? $stat['size'] : 0
                ];
                $redemption->setData('thank_you_image', [$thumbnailImage]);
            }
            $this->loadedData[$redemption->getId()] = $redemption->getData();
        }
        $data = $this->dataPersistor->get('eguana_redemption');
        if (!empty($data)) {
            $redemption = $this->collection->getNewEmptyItem();
            $redemption->setData($data);
            $this->loadedData[$redemption->getId()] = $redemption->getData();
            $this->dataPersistor->clear('eguana_redemption');
        }
        return $this->loadedData;
    }
}
