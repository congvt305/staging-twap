<?php
/**
 * Created by PhpStorm
 * User: Abbas
 * Date: 05/20/20
 * Time: 12:11 PM
 */
namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * This class is responsible for add attribute in customer
 *
 * Class AddUpdateCustomerAttributesThird
 */
class AddUpdateCustomerAttributesFour implements DataPatchInterface
{
    /**
     * Eav Setup
     *
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * AddTelephoneAttribute constructor.
     *
     * @param EavSetup                     $eavSetup                     setup
     */
    public function __construct(
        EavSetup $eavSetup
    ) {
        $this->eavSetup                  = $eavSetup;
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
     * This function is responsible for create telephone attribute
     *
     * @return DataPatchInterface|void
     */
    public function apply()
    {
        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'mobile_number',
            'backend_type',
            'varchar'
        );
    }
}
