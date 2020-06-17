<?php
/**
 * Created by PhpStorm
 * User: Abbas
 * Date: 05/20/20
 * Time: 12:11 PM
 */
namespace Amore\CustomerRegistration\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for add attribute in customer
 *
 * Class AddUpdateCustomerAttributesSecond
 */
class AddUpdateCustomerAttributesSecond implements DataPatchInterface
{

    /**
     * Eav config
     *
     * @var EavConfig
     */
    private $eavConfig;

    /**
     * Eav Setup
     *
     * @var EavSetup
     */
    private $eavSetup;

    /**
     * Customer attribute resource
     *
     * @var CustomerAttributeResource
     */
    private $customerAttributeResource;

    /**
     * Attribute repository interface
     *
     * @var AttributeRepositoryInterface
     */
    private $attributeRepositoryInterface;

    /**
     * Logger interface
     *
     * @var LoggerInterface
     */
    private $logger;

    /**
     * AddTelephoneAttribute constructor.
     *
     * @param EavSetup                     $eavSetup                     setup
     * @param EavConfig                    $eavConfig                    conf
     * @param CustomerAttributeResource    $customerAttributeResource    resource
     * @param AttributeRepositoryInterface $attributeRepositoryInterface repo
     * @param LoggerInterface              $logger                       logger
     */
    public function __construct(
        EavSetup $eavSetup,
        EavConfig $eavConfig,
        CustomerAttributeResource $customerAttributeResource,
        AttributeRepositoryInterface $attributeRepositoryInterface,
        LoggerInterface $logger
    ) {
        $this->eavConfig                 = $eavConfig;
        $this->eavSetup                  = $eavSetup;
        $this->customerAttributeResource = $customerAttributeResource;
        $this->attributeRepositoryInterface = $attributeRepositoryInterface;
        $this->logger = $logger;
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
            'dm_zipcode',
            'backend_type',
            'varchar'
        );
        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'dm_zipcode',
            'validate_rules',
            '{"input_validation":"length", "min_text_length":1,"max_text_length":20}'
        );
        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'dm_detailed_address',
            'validate_rules',
            '{"input_validation":"length", "min_text_length":1,"max_text_length":255}'
        );
        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'favorite_store',
            'validate_rules',
            '{"input_validation":"length", "min_text_length":1,"max_text_length":255}'
        );

        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'referrer_code',
            'validate_rules',
            '{"input_validation":"length", "min_text_length":1,"max_text_length":255}'
        );

        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'sms_subscription_status',
            'source_model',
            \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
        );

        $this->eavSetup->updateAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            'dm_subscription_status',
            'source_model',
            \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class
        );

        $textFieldAttributes = [
                                    [
                                        'code'=>'integration_number',
                                        'label'=>'Inetgration Number',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                        "max_text_length":30,
                                        "min_text_length":13}',
                                        'required' => 0
                                    ],
                                    [
                                        'code'=>'sales_organization_code',
                                        'label'=>'Sales Organization Code',
                                        'type' => 'varchar',
                                        'validation'=>'',
                                        'required' => 0
                                    ],
                                    [
                                        'code'=>'sales_office_code',
                                        'label'=>'Sales Office Code',
                                        'type' => 'varchar',
                                        'validation'=>'',
                                        'required' => 0
                                    ]
                                ];

        $textAreaAttributes = [
            [
                'code'=>'pos_synced_report',
                'label'=>'POS Sync Report',
                'type' => 'varchar',
                'required' => 0
            ]
        ];

        $yesNoAttributes = [
            ['code'=>'pos_synced_successfully','label'=>'Successfully Synced to POS'],
            ['code'=>'imported_from_pos','label'=>'Imported from POS'],
            ['code'=>'call_subscription_status','label'=>'Call Marketing'],
            ['code'=>'status_code','label'=>'Status Code']
                            ];

        foreach ($textFieldAttributes as $textFieldAttribute) {

            $this->addCustomerAttributeTextField(
                $textFieldAttribute['code'],
                $textFieldAttribute['label'],
                $textFieldAttribute['type'],
                $textFieldAttribute['validation'],
                $textFieldAttribute['required']
            );
        }

        foreach ($yesNoAttributes as $yesNoAttribute) {
            $this->addCustomerAttributeBoolean(
                $yesNoAttribute['code'],
                $yesNoAttribute['label']
            );
        }

        foreach ($textAreaAttributes as $textAreaAttribute) {

            $this->addCustomerAttributeTextArea(
                $textAreaAttribute['code'],
                $textAreaAttribute['label'],
                $textAreaAttribute['type'],
                $textAreaAttribute['required']
            );
        }
    }

    /**
     * To create a text field attribute for customer
     * This function will create a text field customer attribute based on code, label, type, validation and required
     *
     * @param string $code       code
     * @param string $label      label
     * @param string $type       type
     * @param string $validation validation
     * @param int    $required   required
     */
    private function addCustomerAttributeTextField($code, $label, $type, $validation, $required)
    {
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
                        'label' => $label,
                        'type' => $type,
                        'input' => 'text',
                        'validate_rules' => $validation,
                        'required' => $required,
                        'system' => 0,
                        'sort_order' => 100,
                        'user_defined' => 1,
                        'group' => 'General'
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            $this->assignAttributeToForms($code);
        }
    }

    /**
     * To create a boolean field attribute for customer
     * This function will create a boolean customer attribute based on code and label
     *
     * @param string $code  code
     * @param string $label label
     *
     * @return void
     */
    private function addCustomerAttributeBoolean($code, $label)
    {
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
                        'label' => $label,
                        'type' => 'int',
                        'input' => 'select',
                        'source' => \Magento\Eav\Model\Entity\Attribute\Source\Boolean::class,
                        'required' => 0,
                        'system' => 0,
                        'sort_order' => 100,
                        'user_defined' => 1,
                        'group' => 'General'
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            $this->assignAttributeToForms($code);
        }
    }

    /**
     * To create a text field attribute for customer
     * This function will create a text field customer attribute based on code, label, type, validation and required
     *
     * @param string $code       code
     * @param string $label      label
     * @param string $type       type
     * @param int    $required   required
     */
    private function addCustomerAttributeTextArea($code, $label, $type, $required)
    {
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
                        'label' => $label,
                        'type' => $type,
                        'input' => 'textarea',
                        'required' => $required,
                        'system' => 0,
                        'sort_order' => 100,
                        'user_defined' => 1,
                        'group' => 'General'
                    ]
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }

            $this->assignAttributeToForms($code);
        }
    }

    /**
     * This function is responsible for add attribute to create account page
     *
     * @param string $code code
     *
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
            if (in_array($code, ['pos_synced_successfully', 'pos_synced_report', 'customer_integration_number', 'status_code'])) {
                $forms = [
                    'adminhtml_customer'
                ];
            }
            $attribute->setData(
                'used_in_forms',
                $forms
            );

            $this->customerAttributeResource->save($attribute);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
    }
}
