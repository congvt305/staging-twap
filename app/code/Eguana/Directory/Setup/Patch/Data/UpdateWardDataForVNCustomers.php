<?php

namespace Eguana\Directory\Setup\Patch\Data;

use Eguana\Directory\Model\ResourceModel\Ward\Collection;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Patch is mechanism, that allows to do atomic upgrade data changes
 */
class UpdateWardDataForVNCustomers implements DataPatchInterface
{
    /**
     * Csv
     *
     * @var \Magento\Framework\File\Csv
     */
    protected $fileCsv;

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory
     */
    protected $customerCollection;

    /**
     * @var \Eguana\Directory\Helper\Data
     */
    protected $helper;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @var \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory
     */
    protected $wardCollection;

    public function __construct(
        \Magento\Framework\File\Csv $fileCsv,
        \Magento\Customer\Model\ResourceModel\Customer\CollectionFactory $collectionFactory,
        \Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory $wardCollectionFactory,
        \Eguana\Directory\Helper\Data $helper,
        \Psr\Log\LoggerInterface $logger,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->fileCsv = $fileCsv;
        $this->customerCollection = $collectionFactory;
        $this->wardCollection = $wardCollectionFactory;
        $this->helper = $helper;
        $this->logger = $logger;
        $this->moduleDataSetup = $moduleDataSetup;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        $this->moduleDataSetup->startSetup();
        $file = __DIR__.'/wards_data_of_customers.csv';
        if ($file) {
            $data = $this->fileCsv->getData($file);
            // get array wards data: customer_integration_number => ward
            $wardsData = [];
            foreach ($data as $item) {
                if (count($item) > 1) {
                    $wardsData[trim($item[0])] = trim($item[1]);
                }
            }

            if ($wardsData) {
                // get customer/ addresses by customer_integration_number
                $customerCollection = $this->customerCollection->create()->addAttributeToFilter('integration_number', ['in' => array_keys($wardsData)]);
                if ($customerCollection && $customerCollection->getSize() > 0) {
                    $this->logger->info('Start import Wards');
                    $this->logger->info('Total customer will import ' . $customerCollection->getSize());
                    foreach ($customerCollection as $customer) {
                        try {
                            $this->logger->info('Customer Id ' . $customer->getId());
                            $address = $customer->getDefaultShippingAddress();
                            if ($address && $address->getId()) {
                                // Check if has address by city_id and ward = $wardsData[ntegration_number] same with table directory_...._ward
                                $cityId = $this->helper->getCityIdByRegionName($address->getRegionId(), $address->getCity());
                                $integrationNumber = $customer->getIntegrationNumber();
                                $this->logger->info('Customer integration Number ' . $integrationNumber);
                                if (isset($wardsData[$integrationNumber]) && $wardsData[$integrationNumber]) {
                                    $ward = $wardsData[$integrationNumber];
                                    $wardCollection = $this->wardCollection->create()->addFieldToFilter('default_name', ['like' => $ward])
                                        ->addFieldToFilter('city_id', $cityId);
                                    $this->logger->info('CityId ' . $cityId . ' ward ' . $ward);
                                    if ($wardCollection && $wardCollection->getSize() > 0) {
                                        $wardItem = $wardCollection->getFirstItem();
                                        if ($wardItem && $wardItem->getId()) {
                                            // set CityId, WardId and Ward Data to customer address from table directory_...._ward
                                            $address->setCustomAttribute('ward_id', $wardItem->getWardId())
                                                ->setCustomAttribute('city_id', $cityId)
                                                ->setCustomAttribute('ward', $wardItem->getDefaultName());
                                            // Save address
                                            $address->save();
                                            $this->logger->info('Save Addres id ' . $address->getId() . ' successful ');
                                            continue;
                                        }
                                    }
                                }
                            }
                            $this->logger->info('Customer Id ' . $customer->getId() . ' save address failed');
                        } catch ( \Exception $exception) {
                            $this->logger->info('cannot import ' . $exception->getMessage());
                        }
                    }
                }
            }
        }
        $this->moduleDataSetup->endSetup();
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [\Eguana\Directory\Setup\Patch\Data\UpdateNewWardDataForVietnam::class];
    }
}

