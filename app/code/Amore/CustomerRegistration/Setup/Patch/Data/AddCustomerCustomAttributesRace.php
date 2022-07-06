<?php

declare(strict_types=1);

namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * Class AddCustomerCustomAttributesRace
 */
class AddCustomerCustomAttributesRace implements DataPatchInterface
{
    const ATTRIBUTE_CODE = 'race';
    const ATTRIBUTE_TITLE = 'Race';

    /**
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * @var CustomerAttributeResource
     */
    private $customerAttributeResource;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param EavSetup $eavSetup
     * @param EavConfig $eavConfig
     * @param CustomerAttributeResource $customerAttributeResource
     * @param LoggerInterface $logger
     */
    public function __construct(
        EavSetup $eavSetup,
        EavConfig $eavConfig,
        CustomerAttributeResource $customerAttributeResource,
        LoggerInterface $logger
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetup = $eavSetup;
        $this->customerAttributeResource = $customerAttributeResource;
        $this->logger = $logger;
    }

    /**
     * {@inheritDoc}
     */
    public function apply()
    {
        $attribute = $this->customerAttributeResource->getIdByCode(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            self::ATTRIBUTE_CODE
        );

        if (!$attribute) {
            try {
                $this->eavSetup->addAttribute(
                    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    self::ATTRIBUTE_CODE,
                    [
                        'label' => self::ATTRIBUTE_TITLE,
                        'type' => 'varchar',
                        'position' => 115,
                        'user_defined' => 1,
                        'group' => 'General',
                        'input' => 'select',
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Table::class,
                        'required' => false,
                        'sort_order' => 110,
                        'visible' => true,
                        'system' => false,
                        'validate_rules' => '[]',
                        'option' => ['values' => ['Malay', 'Chinese', 'Indian', 'Others']],
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
            self::ATTRIBUTE_CODE
        );

        $attribute->setData(
            'used_in_forms',
            [
                'adminhtml_customer',
                'customer_account_create',
                'customer_account_edit'
            ]
        );

        $this->customerAttributeResource->save($attribute);
    }

    /**
     * {@inheritDoc}
     */
    public static function getDependencies(): array
    {
        return [];
    }

    /**
     * {@inheritDoc}
     */
    public function getAliases(): array
    {
        return [];
    }
}
