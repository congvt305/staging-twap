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
 * Class AddTelephoneAttribute
 */
class AddCustomerCustomAttributes implements DataPatchInterface
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
        $textFieldAttributes = [
                                    [
                                        'code'=>'integration_number',
                                        'label'=>'Inetgration Number',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                        "max_text_length":30,
                                        "min_text_length":13}',
                                        'required' => 0,
                                        'sort_order' => 130
                                    ],
                                    [
                                        'code'=>'mobile_number',
                                        'label'=>'Mobile number',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"alphanumeric",
                                        "max_text_length":20,
                                        "min_text_length":5}',
                                        'required' => 1,
                                        'sort_order' => 131
                                    ],
                                    [
                                        'code'=>'sales_organization_code',
                                        'label'=>'Sales Organization Code',
                                        'type' => 'varchar',
                                        'validation'=>'',
                                        'required' => 0,
                                        'sort_order' => 132
                                    ],
                                    [
                                        'code'=>'sales_office_code',
                                        'label'=>'Sales Office Code',
                                        'type' => 'varchar',
                                        'validation'=>'',
                                        'required' => 0,
                                        'sort_order' => 133
                                    ],
                                    [
                                        'code'=>'partner_id',
                                        'label'=>'Partner Id',
                                        'type' => 'varchar',
                                        'validation'=>'',
                                        'required' => 0,
                                        'sort_order' => 134
                                    ],
                                    [
                                        'code'=>'favorite_store',
                                        'label'=>'Favorite Store',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                                                "min_text_length":1,
                                                                "max_text_length":255}',
                                        'required' => 0,
                                        'sort_order' => 135
                                    ],
                                    [
                                        'code'=>'referrer_code',
                                        'label'=>'Referrer Code',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                                                "min_text_length":1,
                                                                "max_text_length":255}',
                                        'required' => 0,
                                        'sort_order' => 136
                                    ],
                                    [
                                        'code'=>'dm_city',
                                        'label'=>'DM City',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                        "min_text_length":1,
                                        "max_text_length":255}',
                                        'required' => 0,
                                        'sort_order' => 143
                                    ],
                                    [
                                        'code'=>'dm_state',
                                        'label'=>'DM State',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                        "min_text_length":1,
                                        "max_text_length":255}',
                                        'required' => 0,
                                        'sort_order' => 144
                                    ],
                                    [
                                        'code'=>'dm_detailed_address',
                                        'label'=>'DM Detailed Address',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length",
                                        "min_text_length":1,
                                        "max_text_length":255}',
                                        'required' => 0,
                                        'sort_order' => 145
                                    ],
                                    [
                                        'code'=>'dm_zipcode',
                                        'label'=>'DM Zipcode',
                                        'type' => 'varchar',
                                        'validation'=>'{"input_validation":"length", 
                                        "min_text_length":1,
                                        "max_text_length":20}',
                                        'required' => 0,
                                        'sort_order' => 146
                                    ]
                                ];
        $yesNoAttributes = [
            ['code'=>'imported_from_pos','label'=>'Imported from POS',
                'sort_order' => 137],
            ['code'=>'status_code','label'=>'Status Code',
                'sort_order' => 138],
            ['code'=>'terms_and_services_policy','label'=>'Terms And Services Policy',
                'sort_order' => 139],
            ['code'=>'sms_subscription_status','label'=>'SMS Marketing',
                'sort_order' => 140],
            ['code'=>'call_subscription_status','label'=>'Call Marketing',
                'sort_order' => 141],
            ['code'=>'dm_subscription_status','label'=>'DM Marketing',
                'sort_order' => 142]
                            ];

        foreach ($textFieldAttributes as $textFieldAttribute) {

            $this->addCustomerAttributeTextFiled(
                $textFieldAttribute['code'],
                $textFieldAttribute['label'],
                $textFieldAttribute['type'],
                $textFieldAttribute['validation'],
                $textFieldAttribute['required'],
                $textFieldAttribute['sort_order']
            );
        }

        foreach ($yesNoAttributes as $yesNoAttribute) {
            $this->addCustomerAttributeBoolean(
                $yesNoAttribute['code'],
                $yesNoAttribute['label'],
                $yesNoAttribute['sort_order']
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
     * @param int    $validation validation
     * @param string $required   required
     */
    private function addCustomerAttributeTextFiled($code, $label, $type, $validation, $required, $sortOrder)
    {
        $this->eavSetup->removeAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $code
        );

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
                        'position' => $sortOrder,
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
    private function addCustomerAttributeBoolean($code, $label, $sortOrder)
    {
        $this->eavSetup->removeAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $code
        );
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
                        'position' => $sortOrder,
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
            $onlyInAdmin = [
                'customer_integration_number',
                'status_code',
                'partner_id',
                'sales_organization_code',
                'sales_office_code'
            ];

            if (in_array($code, $onlyInAdmin)) {
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
