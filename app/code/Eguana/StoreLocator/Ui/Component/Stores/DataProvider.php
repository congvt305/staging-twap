<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: shahroz
 * Date: 28/1/20
 * Time: 2:42 PM
 */
namespace Eguana\StoreLocator\Ui\Component\Stores;

use Eguana\StoreLocator\Model\ResourceModel\StoreInfo\CollectionFactory;
use Magento\Framework\View\Element\UiComponent\DataProvider\FilterPool;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * This class is responsible for form membership rule
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var CollectionFactory
     */
    protected $collection;

    /**
     * @var FilterPool
     */
    private $filterPool;

    /**
     * @var array
     */
    private $loadedData;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param RuleCollectionFactory $collectionFactory
     * @param FilterPool $filterPool
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        FilterPool $filterPool,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
        $this->filterPool = $filterPool;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getData()
    {
        if (!$this->loadedData) {
            $items = $this->collection->getItems();
            $result = [];
            foreach ($items as $item) {
                $result['storeinfo_data'] = $item->getData();
                $this->loadedData[$item->getEntityId()] = $result;
                break;
            }
        }
        return $this->loadedData;
    }
}
