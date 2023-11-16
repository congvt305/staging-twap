<?php

namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Eguana\Directory\Model\ResourceModel\Address\Source\City;
use Eguana\Directory\Model\ResourceModel\Address\Source\Ward;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\Patch\Data\DefaultCustomerGroupsAndAttributes;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Amore\CustomerRegistration\Model\Source\CountryId;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class AddAddressCountryPosCodeAttribute implements DataPatchInterface
{
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;


    /**
     * AddWardAttribute constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
    }

    /**
     * Do Upgrade
     *
     * @return void
     */
    public function apply()
    {
        /** @var CustomerSetup $customerSetup */
        $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);

        $posCodeAttrInfo = [
            'type' => 'static',
            'label' => 'Country Pos Code',
            'input' => 'select',
            'source' => CountryId::class,
            'required' => true,
            'sort_order' => 90,
            'position' => 90,
            'system' =>  0,
        ];

        $customerSetup->addAttribute('customer_address', 'country_pos_code', $posCodeAttrInfo);
        $attribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'country_pos_code');
        $usedInForms = [
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address'
        ];
        foreach ($usedInForms as $formCode) {
            $data[] = ['form_code' => $formCode, 'attribute_id' => $attribute->getId()];
        }
        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $data);

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
        return [
            DefaultCustomerGroupsAndAttributes::class,
        ];
    }
}
