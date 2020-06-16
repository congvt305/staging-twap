<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

namespace Eguana\Directory\Setup\Patch\Data;

use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;

/**
* Patch is mechanism, that allows to do atomic upgrade data changes
*/
class AddCityIdAttribute implements DataPatchInterface
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
     * AddCityIdAttribute constructor.
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
        $customerSetup->updateAttribute(
            'customer_address',
            'city',
            'backend_model',
            \Eguana\Directory\Model\ResourceModel\Address\Backend\City::class
        );
        $cityIdAttrInfo = [
            'type' => 'static',
            'label' => 'City/District',
            'input' => 'hidden',
            'source' => \Eguana\Directory\Model\ResourceModel\Address\Source\City::class,
            'required' => false,
            'sort_order' => 80,
            'position' => 80,
            'is_used_in_grid' => false,
            'is_visible_in_grid' => false,
            'is_filterable_in_grid' => false,
            'is_searchable_in_grid' => false,
        ];
        $customerSetup->addAttribute('customer_address', 'city_id', $cityIdAttrInfo);
        $cityIdAttribute = $customerSetup->getEavConfig()->getAttribute('customer_address', 'city_id');
        //Magento\Eav\Model\ResourceModel\Entity\Attribute as AttributeResource;
        $cityIdAttribute->setData(
            'used_in_forms',
            [
                'customer_address_edit',
                'customer_register_address',
                'customer_account_create',
                'checkout_register',
                'adminhtml_customer',
                'adminhtml_checkout',
                'adminhtml_customer_address',
                'checkout_onepage_register',
                'checkout_onepage_register_guest',
                'checkout_onepage_billing_address',
                'checkout_onepage_shipping_address',
            ]
        );
        $cityIdAttribute->save();
//        $this->attributeResource->save($cityIdAttribute);
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
            \Magento\Customer\Setup\Patch\Data\DefaultCustomerGroupsAndAttributes::class,
        ];
    }
}
