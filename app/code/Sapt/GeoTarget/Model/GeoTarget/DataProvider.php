<?php
namespace Sapt\GeoTarget\Model\GeoTarget;

use Sapt\GeoTarget\Model\ResourceModel\GeoTarget\CollectionFactory;
use Sapt\GeoTarget\Model\GeoTarget;
use Magento\Store\Model\StoreManagerInterface;

class DataProvider extends \Magento\Ui\DataProvider\AbstractDataProvider
{
    protected $collection;
    protected $_loadedData;

    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $geoTargetCollectionFactory,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        array $meta = [],
        array $data = []
    ) {
        $this->collection = $geoTargetCollectionFactory->create();
        $this->storeManager = $storeManager;
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
    }

    public function getData()
    {
        if(isset($this->_loadedData)) {
            return $this->_loadedData;
        }

        $items = $this->collection->getItems();
        foreach($items as $_geoTarget) {
            $this->_loadedData[$_geoTarget->getId()] = $_geoTarget->getData();
        }

        return $this->_loadedData;
    }

}
