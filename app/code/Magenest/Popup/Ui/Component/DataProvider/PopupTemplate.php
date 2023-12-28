<?php
namespace Magenest\Popup\Ui\Component\DataProvider;

use Magenest\Popup\Model\ResourceModel\Template\CollectionFactory;
use Magento\Ui\DataProvider\AbstractDataProvider;

class PopupTemplate extends AbstractDataProvider
{
    /** @var array|null */
    protected $_loadedData = null;

    /**
     * Popup constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        $meta = [],
        $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->collection = $collectionFactory->create();
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        if (!isset($this->_loadedData)) {
            foreach ($this->getCollection()->getItems() as $item) {
                $this->_loadedData[$item->getId()] = $item->getData();
            }
        }

        return $this->_loadedData;
    }
}
