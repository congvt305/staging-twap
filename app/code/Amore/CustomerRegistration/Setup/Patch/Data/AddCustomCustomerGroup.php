<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 10/12/20
 * Time: 3:43 PM
 */
namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Model\GroupFactory;
use Psr\Log\LoggerInterface;

/**
 * Class AddCustomCustomerGroup
 *
 * Add custom customer groups
 */
class AddCustomCustomerGroup implements DataPatchInterface
{
    /**
     * @var GroupFactory
     */
    private $groupFactory;

    /**
     * Eav Setup
     *
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AddCustomCustomerGroup constructor.
     * @param GroupFactory $groupFactory
     * @param EavSetup $eavSetup
     * @param LoggerInterface $logger
     */
    public function __construct(
        GroupFactory $groupFactory,
        EavSetup $eavSetup,
        LoggerInterface $logger
    ) {
        $this->groupFactory              = $groupFactory;
        $this->eavSetup                  = $eavSetup;
        $this->logger                    = $logger;
    }

    /**
     * Get dependencies
     *
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * Get aliases
     *
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * Add custom customer groups
     * @return AddCustomCustomerGroup|void
     */
    public function apply()
    {
        $groups = ['Snow Water', 'Snow Crystal', 'Snow Diamond', 'Guest', 'Pre-Member', 'VIP', 'VVIP'];
        foreach ($groups as $group) {
            try {
                $groupObj = $this->groupFactory->create();
                $groupObj->setCode($group)
                    ->setTaxClassId(3)
                    ->save();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }
    }
}
