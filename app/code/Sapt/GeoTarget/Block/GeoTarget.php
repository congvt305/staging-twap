<?php
// @codingStandardsIgnoreFile

namespace Sapt\GeoTarget\Block;

use Magento\Catalog\Helper\Data;
use Magento\Framework\View\Element\Template\Context;
use Sapt\GeoTarget\Model\ResourceModel\GeoTarget\CollectionFactory as GeoTargetCollectionFactory;

class GeoTarget extends \Magento\Framework\View\Element\Template
{
    const DEFAULT_STORE_ID = 0;

    protected $_catalogData = null;
    protected $_geoTargetCollectionFactory;

    public function __construct(
        Context $context,
        Data $catalogData,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\ObjectManagerInterface $objectmanager,
        GeoTargetCollectionFactory $_geoTargetCollectionFactory,
        array $data = []
    )
    {
        $this->_catalogData = $catalogData;
        $this->_coreRegistry = $registry;
        $this->_objectManager = $objectmanager;
        $this->_geoTargetCollectionFactory = $_geoTargetCollectionFactory;
        parent::__construct($context, $data);
    }

    public function getUrlPath()
    {
        return parse_url($this->getUrl('*/*/*', ['_current' => true, '_use_rewrite' => true]), PHP_URL_PATH);
    }

    public function getTag()
    {
        $storeId = $this->_storeManager->getStore()->getId();
        $path = $this->getUrlPath();
        $geoData = $this->_geoTargetCollectionFactory->create()
                    ->getStoreIdWithPathFilter($storeId, $path);

        $geoElement = (isset($geoData['geo_tag'])) ?$geoData['geo_tag'] : '';

        return $geoElement;
    }
}
