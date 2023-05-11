<?php
declare(strict_types=1);

namespace Eguana\Directory\Controller\Checkout;

use Magento\Framework\App\Action\Context;
use Eguana\Directory\Model\ResourceModel\City\CollectionFactory as CityCollection;
use Magento\Framework\Json\Helper\Data as JsonData;

class GetCityids extends \Magento\Framework\App\Action\Action
{
    /**
     * @var CityCollection
     */
    protected $_cityCollection;

    /**
     * @var JsonData
     */
    protected $jsonHelper;

    /**
     * @param Context $context
     * @param JsonData $jsonHelper
     * @param CityCollection $_cityCollection
     */
    public function __construct(
        Context $context,
        JsonData $jsonHelper,
        CityCollection $_cityCollection
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_cityCollection = $_cityCollection;
        parent::__construct($context);
    }

    /**
     * Change city options
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $cities = [];
        $regionId = $this->getRequest()->getParam('region_id');
        $collection = $this->_cityCollection->create();
        $collection->addFieldToSelect('city_id')->addFieldToSelect('default_name')->addFieldToSelect('code')
            ->addRegionIdFilter($regionId)
            ->getSelect()->order('city_id asc');
        foreach ($collection as $city) {
            $cities[] =
                ['value' => $city->getCityId(), 'label' => $city->getName(), 'code' => $city->getCode()];
        }

        $citiesData = $this->jsonHelper->jsonEncode($cities);
        return $this->getResponse()->setBody($citiesData);
    }
}
