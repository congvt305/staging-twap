<?php

namespace Eguana\Directory\Setup\Patch\Data;

use Eguana\Directory\Model\ResourceModel\Address\Source\City;
use Eguana\Directory\Model\ResourceModel\Address\Source\Ward;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Customer\Setup\Patch\Data\DefaultCustomerGroupsAndAttributes;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class AddWardAttribute implements DataPatchInterface
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
     * @var AttributeResource
     */
    private $attributeResource;


    /**
     * AddWardAttribute constructor.
     * @param CustomerSetupFactory $customerSetupFactory
     * @param AttributeResource $attributeResource
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        CustomerSetupFactory $customerSetupFactory,
        AttributeResource $attributeResource,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->attributeResource = $attributeResource;
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
        $customerSetup->removeAttribute(
            'customer_address',
            'city_id'
        );
        $wardIdAttrInfo = [
            'type' => 'static',
            'label' => 'Ward Name',
            'input' => 'hidden',
            'source' => Ward::class,
            'required' => false,
            'sort_order' => 80,
            'position' => 80,
            'system'   => 0
        ];

        $customerSetup->addAttribute('customer_address', 'ward_id', $wardIdAttrInfo);
        $wardIdAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'ward_id');
        $usedInForms = [
            'adminhtml_customer_address',
            'customer_address_edit',
            'customer_register_address',
        ];
        foreach ($usedInForms as $formCode) {
            $data[] = ['form_code' => $formCode, 'attribute_id' => $wardIdAttribute->getId()];
        }

        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $data);

        $customerSetup->addAttribute('customer_address', 'ward', [
            'type'          => 'varchar',
            'label'         => 'Ward Name',
            'input'         => 'hidden',
            'required'      =>  false,
            'sort_order'    =>  81,
            'position'      =>  81,
            'system'        =>  0,
        ]);

        $wardAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'ward');
        foreach ($usedInForms as $formCode) {
            $dataWard[] = ['form_code' => $formCode, 'attribute_id' => $wardAttribute->getId()];
        }

        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $dataWard);

        $cityIdAttrInfo = [
            'type' => 'static',
            'label' => 'City/District',
            'input' => 'hidden',
            'source' => City::class,
            'required' => false,
            'sort_order' => 80,
            'position' => 80,
            'system' =>  0,
        ];

        $customerSetup->addAttribute('customer_address', 'city_id', $cityIdAttrInfo);
        $cityIdAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'city_id');
        foreach ($usedInForms as $formCode) {
            $dataCity[] = ['form_code' => $formCode, 'attribute_id' => $cityIdAttribute->getId()];
        }
        $this->moduleDataSetup->getConnection()
            ->insertMultiple($this->moduleDataSetup->getTable('customer_form_attribute'), $dataCity);

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
