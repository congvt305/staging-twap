<?php

namespace CJ\Migrate\Setup\Patch\Data;

use Magento\Customer\Api\AddressMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddHKCustomerAddressAttributeStreet
 * @package CJ\Migrate\Setup\Patch\Data
 */
class AddHKCustomerAddressAttributeStreet implements DataPatchInterface
{
    const ATTRIBUTE_CODE_STREET_2 = 'street_2';
    /**
     * @var ModuleDataSetupInterface $moduleDataSetup
     */
    private $moduleDataSetup;
    /**
     * @var CustomerSetupFactory
     */
    private $customerSetupFactory;
    /**
     * @var CustomerAttributeResource
     */
    private $customerAttributeResource;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * AbstractHKCustomerAttribute constructor.
     * @param EavConfig $eavConfig
     * @param LoggerInterface $logger
     * @param CustomerAttributeResource $customerAttributeResource
     * @param CustomerSetupFactory $customerSetupFactory
     * @param ModuleDataSetupInterface $moduleDataSetup
     */
    public function __construct(
        EavConfig $eavConfig,
        LoggerInterface $logger,
        CustomerAttributeResource $customerAttributeResource,
        CustomerSetupFactory $customerSetupFactory,
        ModuleDataSetupInterface $moduleDataSetup
    ) {
        $this->moduleDataSetup = $moduleDataSetup;
        $this->customerSetupFactory = $customerSetupFactory;
        $this->customerAttributeResource = $customerAttributeResource;
        $this->logger = $logger;
        $this->eavConfig = $eavConfig;
    }
    /**
     * {@inheritDoc}
     */
    public function apply()
    {
        $attribute = $this->customerAttributeResource->getIdByCode(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            self::ATTRIBUTE_CODE_STREET_2
        );

        if (!$attribute) {
            try {
                /** @var CustomerSetup $customerSetup */
                $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
                $customerSetup->addAttribute(
                    AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
                    self::ATTRIBUTE_CODE_STREET_2,
                    [
                        'type' => 'text',
                        'label' => 'Street Address',
                        'input' => 'multiline',
                        'user_defined' => true,
                        'required' => false,
                        'system' => false,
                        'multiline_count' => 3,
                        'is_visible' => false,
                        'is_used_for_customer_segment' => true,
                        'position' => 75
                    ]
                );
                $this->assignAttributeToForms();
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
        }

        return $this;
    }

    /**
     * Assign attribute to Magento forms
     *
     * @return void
     * @throws \Magento\Framework\Exception\AlreadyExistsException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    private function assignAttributeToForms(): void
    {
        $attribute = $this->eavConfig->getAttribute(
            AddressMetadataInterface::ENTITY_TYPE_ADDRESS,
            self::ATTRIBUTE_CODE_STREET_2
        );

        $attribute->setData(
            'used_in_forms',
            [
                'customer_register_address',
                'customer_address_edit'
            ]
        );

        $this->customerAttributeResource->save($attribute);
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
}
