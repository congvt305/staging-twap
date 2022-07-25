<?php

namespace CJ\Migrate\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Customer\Setup\CustomerSetup;
use Magento\Customer\Setup\CustomerSetupFactory;
use Psr\Log\LoggerInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;

/**
 * Class AddHKCustomerAttribute
 * @package CJ\Migrate\Setup\Patch\Data
 */
class AddHKCustomerAttributeBeautyCode implements DataPatchInterface
{
    const ATTRIBUTE_CODE_BEAUTY_PROFILE = 'beauty_profile';
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
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::ATTRIBUTE_CODE_BEAUTY_PROFILE
        );

        if (!$attribute) {
            try {
                /** @var CustomerSetup $customerSetup */
                $customerSetup = $this->customerSetupFactory->create(['setup' => $this->moduleDataSetup]);
                $customerSetup->addAttribute(
                    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    self::ATTRIBUTE_CODE_BEAUTY_PROFILE,
                    [
                        'type' => 'varchar',
                        'label' => 'Beauty Profile',
                        'input' => 'multiselect',
                        'backend_model' => \Magento\Eav\Model\Entity\Attribute\Backend\ArrayBackend::class,
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                        'user_defined' => true,
                        'required' => false,
                        'system' => false,
                        'option' => ['values' => ['Anti-aging', 'Skin moisture', 'Anti-oxidation', 'Vitality', 'Lifting/Wrinkle care', 'Nutrition']],
                        'position' => 10
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
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::ATTRIBUTE_CODE_BEAUTY_PROFILE
        );

        $attribute->setData(
            'used_in_forms',
            [
                'customer_account_edit'
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
