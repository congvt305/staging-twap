<?php

namespace Eguana\Dhl\Block\Adminhtml\Carrier\Tablerate;

class Grid extends \Magento\Backend\Block\Widget\Grid\Extended
{
    /**
     * Website filter
     *
     * @var int
     */
    protected $_websiteId;

    /**
     * Condition filter
     *
     * @var string
     */
    protected $_conditionName;

    /**
     * @var \Eguana\Dhl\Model\Carrier\Tablerate
     */
    protected $_tablerate;

    /**
     * @var \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * @param \Magento\Backend\Block\Template\Context $context
     * @param \Magento\Backend\Helper\Data $backendHelper
     * @param \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate\CollectionFactory $collectionFactory
     * @param \Eguana\Dhl\Model\Carrier\Tablerate
     * @param array $data
     */
    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        \Magento\Backend\Helper\Data $backendHelper,
        \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate\CollectionFactory $collectionFactory,
        \Eguana\Dhl\Model\Carrier\Tablerate $tablerate,
        array $data = []
    ) {
        $this->_collectionFactory = $collectionFactory;
        $this->_tablerate = $tablerate;
        parent::__construct($context, $backendHelper, $data);
    }

    /**
     * Define grid properties
     *
     * @return void
     */
    protected function _construct()
    {
        parent::_construct();
        $this->setId('eguanaDhlTablerateGrid');
        $this->_exportPageSize = 10000;
    }

    /**
     * Set current website
     *
     * @param int $websiteId
     * @return $this
     */
    public function setWebsiteId($websiteId)
    {
        $this->_websiteId = $this->_storeManager->getWebsite($websiteId)->getId();
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        if ($this->_websiteId === null) {
            $this->_websiteId = $this->_storeManager->getWebsite()->getId();
        }
        return $this->_websiteId;
    }

    /**
     * Set current website
     *
     * @param string $name
     * @return $this
     */
    public function setConditionName($name)
    {
        $this->_conditionName = $name;
        return $this;
    }

    /**
     * Retrieve current website id
     *
     * @return int
     */
    public function getConditionName()
    {
        return $this->_conditionName;
    }

    /**
     * Prepare shipping table rate collection
     *
     * @return \Eguana\Dhl\Block\Adminhtml\Carrier\Tablerate\Grid
     */
    protected function _prepareCollection()
    {
        /** @var $collection \Eguana\Dhl\Model\ResourceModel\Carrier\Tablerate\Collection */
        $collection = $this->_collectionFactory->create();
        $collection->setConditionFilter($this->getConditionName())->setWebsiteFilter($this->getWebsiteId());

        $this->setCollection($collection);

        return parent::_prepareCollection();
    }

    /**
     * Prepare table columns
     *
     * @return \Magento\Backend\Block\Widget\Grid\Extended
     */
    protected function _prepareColumns()
    {
        $this->addColumn(
            'dest_country',
            ['header' => __('Country'), 'index' => 'dest_country', 'default' => '*']
        );

        $this->addColumn(
            'dest_region',
            ['header' => __('Region/State'), 'index' => 'dest_region', 'default' => '*']
        );

        $this->addColumn(
            'dest_city',
            ['header' => __('City/District'), 'index' => 'dest_city', 'default' => '*']
        );

        $label = $this->_tablerate->getCode('condition_name_short', $this->getConditionName());
        $this->addColumn('condition_value', ['header' => $label, 'index' => 'condition_value']);

        $this->addColumn('price', ['header' => __('Shipping Price'), 'index' => 'price']);

        return parent::_prepareColumns();
    }

}
