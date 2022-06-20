<?php

namespace CJ\CouponCustomer\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\GroupFactory;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Framework\Setup\Patch\PatchVersionInterface;
use CJ\CouponCustomer\Logger\Logger;
use CJ\CouponCustomer\Helper\Data as HelperData;

class ImportPOSCustomerGrade implements DataPatchInterface, PatchVersionInterface
{

    /**
     * @var ModuleDataSetupInterface
     */
    private $moduleDataSetup;

    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * @var Logger
     */
    private $logger;


    /**
     * Constant list POS customer grade
     */
    const POS_CUSTOMER_GROUP_LIST = [
        ['code' => 'TWL0001', 'name' => 'GUEST'],
        ['code' => 'TWL0002', 'name' => 'Snow Water'],
        ['code' => 'TWL0003', 'name' => 'Snow Crystal'],
        ['code' => 'TWL0004', 'name' => 'Snow Diamond'],
        ['code' => 'TWS0015', 'name' => 'Guest'],
        ['code' => 'TWS0011', 'name' => 'Pre-Member'],
        ['code' => 'TWS0012', 'name' => 'General'],
        ['code' => 'TWS0013', 'name' => 'VIP'],
        ['code' => 'TWS0014', 'name' => 'VVIP']
    ];

    /**
     * @param ModuleDataSetupInterface $moduleDataSetup
     * @param GroupFactory $groupFactory
     * @param Logger $logger
     */
    public function __construct(
        ModuleDataSetupInterface $moduleDataSetup,
        GroupFactory $groupFactory,
        Logger $logger

    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->groupFactory = $groupFactory;
        $this->logger = $logger;
    }

    /**
     * {@inheritdoc}
     */
    public function apply()
    {
        $this->moduleDataSetup->getConnection()->startSetup();
        try {
            $code = '';
            $name = '';
            $posCustomerGroups = self::POS_CUSTOMER_GROUP_LIST;
            foreach ($posCustomerGroups as $posCustomerGroup) {
                $code = $posCustomerGroup['code'];
                $name = $posCustomerGroup['name'];
                $prefix = $this->getPrefix($code);
                $posCustomerGradeGroup = $prefix . '_' . $name;
                $group = $this->groupFactory->create();
                $group->setCode($posCustomerGradeGroup);
                $group->save();
            }
        } catch (\Exception $exception) {
            $this->logger->info('Fail to create customer group: ' . $exception->getMessage());
            $this->logger->info('code: ' . $code . ' name: ' . $name);
        }
        $this->moduleDataSetup->getConnection()->endSetup();
    }

    /**
     * Prepare prefix for creating customer group
     *
     * @param string $customerGradeCode
     * @return false|string
     */
    public function getPrefix($customerGradeCode)
    {
        return !empty($customerGradeCode) ? substr($customerGradeCode, 0 , 3) : '';
    }

    /**
     * @return void
     */
    public function revert()
    {
    }

    /**
     * {@inheritdoc}
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * {@inheritdoc}
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return string
     */
    public static function getVersion()
    {
        return '1.0';
    }
}
