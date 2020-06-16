<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/14/20
 * Time: 6:26 AM
 */

namespace Eguana\Directory\Model\ResourceModel\Address\Backend;


class City extends \Magento\Eav\Model\Entity\Attribute\Backend\AbstractBackend
{
    /**
     * @var \Eguana\Directory\Model\CityFactory
     */
    private $cityFactory;
    /**
     * @var \Eguana\Directory\Model\ResourceModel\City
     */
    private $cityResource;

    public function __construct(
        \Eguana\Directory\Model\CityFactory $cityFactory,
        \Eguana\Directory\Model\ResourceModel\City $cityResource
    ) {
        $this->cityFactory = $cityFactory;
        $this->cityResource = $cityResource;
    }

    public function beforeSave($object)
    {
        $city = $object->getData('city');
        if (is_numeric($city)) {
            $cityModel = $this->cityFactory->create();
            $this->cityResource->load($cityModel,$city);
            if ($cityModel->getId() && $object->getRegionId() == $cityModel->getRegionId()) {
                $object->setCityId($cityModel->getCityId())->setCity($cityModel->getName());
            }
        }
        return $this;

    }

}
