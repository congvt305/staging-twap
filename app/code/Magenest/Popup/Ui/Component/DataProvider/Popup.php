<?php
namespace Magenest\Popup\Ui\Component\DataProvider;

use Magenest\Popup\Model\ResourceModel\Popup\CollectionFactory;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Ui\DataProvider\AbstractDataProvider;

class Popup extends AbstractDataProvider
{
    /** @var array */
    protected $_loadedData;

    /** @var CollectionFactory */
    protected $_collectionFactory;

    /** @var Json */
    protected $_json;

    /**
     * Popup constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param Json $json
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        Json $json,
        $meta = [],
        $data = []
    ) {
        parent::__construct($name, $primaryFieldName, $requestFieldName, $meta, $data);
        $this->_collectionFactory = $collectionFactory;
        $this->collection = $this->_collectionFactory->create();
        $this->_json = $json;
    }

    /**
     * Get Data
     *
     * @return array
     */
    public function getData()
    {
        if (isset($this->_loadedData)) {
            return $this->_loadedData;
        }
        $items = $this->collection->getItems();
        foreach ($items as $item) {
            $data = $item->getData();
            $data['background_image'] = $this->_json->unserialize($item->getData('background_image') ?? 'null');
            $this->_loadedData[$item->getId()] = $data;
            unset($this->_loadedData[$item->getId()]['widget_instance']);
        }
        return $this->_loadedData;
    }
}
