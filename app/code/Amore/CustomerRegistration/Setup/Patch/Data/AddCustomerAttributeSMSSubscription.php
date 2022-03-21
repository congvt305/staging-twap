<?php

namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

class AddCustomerAttributeSMSSubscription implements DataPatchInterface
{
    const SMS_SUBSCRIPTION_STATUS = 'sms_subscription_status';
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
     * @param LoggerInterface $logger
     * @param CustomerAttributeResource $customerAttributeResource
     */
    public function __construct(
        EavSetup $eavSetup,
        EavConfig $eavConfig,
        LoggerInterface $logger,
        CustomerAttributeResource $customerAttributeResource
    ) {
        $this->logger = $logger;
        $this->eavSetup = $eavSetup;
        $this->eavConfig = $eavConfig;
        $this->customerAttributeResource = $customerAttributeResource;
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
     * To add BA Code attribute for customer
     *
     * @return AddCustomerCustomAttributesBACode|void
     */
    public function apply()
    {
        $attribute = $this->customerAttributeResource
            ->getIdByCode(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                self::SMS_SUBSCRIPTION_STATUS
            );

        if (!$attribute) {
            try {
                $this->eavSetup->addAttribute(
                    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    self::SMS_SUBSCRIPTION_STATUS,
                    [
                        'label' => 'SMS Marketing',
                        'type' => 'int',
                        'input' => 'boolean',
                        'validate_rules' => '',
                        'required' => 0,
                        'position' => 141,
                        'visible' => true,
                        'user_defined' => 1,
                        'group' => 'General',
                        'source' => Boolean::class,
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
                        'system' => 0
                    ]
                );

                $this->assignAttributeToForms();
            } catch (\Exception $e) {
                $this->logger->error("Error while adding customer " . self::SMS_SUBSCRIPTION_STATUS . $e->getMessage());
            }
        }
    }

    /**
     * @return void
     */
    private function assignAttributeToForms()
    {
        try {
            $attribute = $this->eavConfig->getAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                self::SMS_SUBSCRIPTION_STATUS
            );

            $forms = [
                'customer_account_create',
                'customer_account_edit',
                'adminhtml_customer'
            ];

            $attribute->setData(
                'used_in_forms',
                $forms
            );

            $this->customerAttributeResource->save($attribute);
        } catch (\Exception $e) {
            $this->logger->error("Error while assigning attribute to forms" . $e->getMessage());
        }
    }
}
