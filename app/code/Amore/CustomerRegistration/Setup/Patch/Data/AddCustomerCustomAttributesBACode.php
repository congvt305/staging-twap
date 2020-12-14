<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 7/12/2020
 * Time: 4:20 PM
 */
namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible to add BA Code attribute for customer
 *
 * Class AddCustomerCustomAttributesBACode
 */
class AddCustomerCustomAttributesBACode implements DataPatchInterface
{
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
        $code = 'ba_code';
        $attribute = $this->customerAttributeResource
            ->getIdByCode(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $code
            );

        if (!$attribute) {
            try {
                $this->eavSetup->addAttribute(
                    CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                    $code,
                    [
                        'label' => 'BA Code',
                        'type' => 'varchar',
                        'input' => 'text',
                        'validate_rules' => '',
                        'required' => 0,
                        'system' => 0,
                        'position' => 140,
                        'user_defined' => 1,
                        'group' => 'General'
                    ]
                );

                $this->assignAttributeToForms($code);
            } catch (\Exception $e) {
                $this->logger->error("Error while adding customer $code" . $e->getMessage());
            }
        }
    }

    /**
     * This function is responsible to add custom attribute in form
     *
     * @param string $code
     * @return void
     */
    private function assignAttributeToForms($code)
    {
        try {
            $attribute = $this->eavConfig->getAttribute(
                CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
                $code
            );

            $forms = [
                'adminhtml_customer',
                'customer_account_create'
            ];

            $attribute->setData(
                'used_in_forms',
                $forms
            );

            $this->customerAttributeResource->save($attribute);
        } catch (\Exception $e) {
            $this->logger->error("Error while assiging attribute to forms" . $e->getMessage());
        }
    }
}
