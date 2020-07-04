<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/15/20
 * Time: 7:19 AM
 */

namespace Eguana\Directory\Plugin\Checkout;


use Magento\Store\Model\StoreManagerInterface;

class DirectoryDataProcessor
{
    private $cityOptions;

    /**
     * @var \Eguana\Directory\Model\ResourceModel\City\CollectionFactory
     */
    private $cityCollectionFactory;

    /**
     * DirectoryDataProcessor constructor.
     * @param \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
     */
    public function __construct(
        \Eguana\Directory\Model\ResourceModel\City\CollectionFactory $cityCollectionFactory
    ) {
        $this->cityCollectionFactory = $cityCollectionFactory;
    }

    /**
     * @param \Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject
     * @param $result
     * @param array $jsLayout
     */
    public function afterProcess(\Magento\Checkout\Block\Checkout\DirectoryDataProcessor $subject, $result, $jsLayout)
    {
        if (isset($result['components']['checkoutProvider']['dictionaries'])) {
            $dictionariesPassed = $result['components']['checkoutProvider']['dictionaries'];
            $cityOptions = ['city_id' => $this->getCityOptions()];
            $result['components']['checkoutProvider']['dictionaries'] = array_merge($dictionariesPassed, $cityOptions);
        }

        return $result;
    }

    private function getCityOptions()
    {
        if (!isset($this->cityOptions)) {
            $this->cityOptions = $this->cityCollectionFactory->create()->addAllowedCountriesFilter()->toOptionArray();
        }
        return $this->cityOptions;
    }
}
