<?php

namespace CJ\CustomCustomer\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Class AddCustomerCustomAttributes
 */
class AddCustomerCustomAttributes
    implements \Magento\Framework\Setup\Patch\DataPatchInterface
{

    /**
     * @var \Magento\Eav\Model\Config
     */
    protected $eavConfig;

    /**
     * @var \Magento\Eav\Setup\EavSetup
     */
    protected $eavSetup;

    /**
     * @var \Magento\Customer\Model\ResourceModel\Attribute
     */
    protected $customerAttributeResource;

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    protected $attributeRepository;

    /**
     * @var \Psr\Log\LoggerInterface
     */
    protected $logger;

    /**
     * @param \Magento\Eav\Model\Config $eavConfig
     * @param \Magento\Eav\Setup\EavSetup $eavSetup
     * @param \Magento\Customer\Model\ResourceModel\Attribute $customerAttributeResource
     * @param \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Magento\Eav\Model\Config $eavConfig,
        \Magento\Eav\Setup\EavSetup $eavSetup,
        \Magento\Customer\Model\ResourceModel\Attribute $customerAttributeResource,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->eavConfig = $eavConfig;
        $this->eavSetup = $eavSetup;
        $this->customerAttributeResource = $customerAttributeResource;
        $this->attributeRepository = $attributeRepository;
        $this->logger = $logger;
    }


    /**
     * @return array|string[]
     */
    public static function getDependencies()
    {
        return [];
    }

    /**
     * @return array|string[]
     */
    public function getAliases()
    {
        return [];
    }

    /**
     * @return AddCustomerCustomAttributes|void
     */
    public function apply()
    {
        $textFieldAttributes = [
            [
                'code'=>'pos_customer_id',
                'label'=>'POS Customer Id',
                'type' => 'varchar',
                'validation' => '{"input_validation":"length","max_text_length": 30,"min_text_length": 13}',
                'required' => 0,
                'sort_order' => 130
            ]
        ];

        foreach ($textFieldAttributes as $textFieldAttribute) {
            $this->addCustomerAttributeTextField(
                $textFieldAttribute['code'],
                $textFieldAttribute['label'],
                $textFieldAttribute['type'],
                $textFieldAttribute['validation'],
                $textFieldAttribute['required'],
                $textFieldAttribute['sort_order']
            );
        }
    }

    /**
     * @param $code
     * @param $label
     * @param $type
     * @param $validation
     * @param $required
     * @param $sortOrder
     * @return void
     */
    protected function addCustomerAttributeTextField($code, $label, $type, $validation, $required, $sortOrder)
    {
        $attribute = $this->customerAttributeResource->getIdByCode(\Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $code);

        if (!$attribute) {
            try {
                $this->eavSetup->addAttribute(
                    \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
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
     * @param $code
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
            $this->logger->error($e->getMessage());
        }
    }
}
