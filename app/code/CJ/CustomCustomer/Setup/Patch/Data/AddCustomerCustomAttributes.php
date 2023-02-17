<?php

namespace CJ\CustomCustomer\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;

/**
 * Class AddCustomerCustomAttributes
 */
class AddCustomerCustomAttributes
    implements \Magento\Framework\Setup\Patch\DataPatchInterface
{
    /**#@+
     * Constants used as keys of data array
     */
    const POS_CSTM_NO = 'pos_cstm_no';
    const LABEL = 'label';
    const TYPE = 'type';
    const VALIDATION = 'validation';
    const REQUIRED = 'required';
    const SORT_ORDER = 'sort_order';
    const IS_USED_IN_GRID = 'is_used_in_grid';
    const IS_VISIBLE_IN_GRID = 'is_visible_in_grid';
    const IS_FILTERABLE_IN_GRID = 'is_filterable_in_grid';
    const IS_SEARCHABLE_IN_GRID = 'is_searchable_in_grid';

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
            self::POS_CSTM_NO => [
                self::LABEL => 'Pos Customer ID',
                self::TYPE => 'static',
                self::VALIDATION => '{"input_validation":"length","max_text_length": 30,"min_text_length": 13}',
                self::REQUIRED => 0,
                self::SORT_ORDER => 130,
                self::IS_USED_IN_GRID => 1,
                self::IS_VISIBLE_IN_GRID => 1,
                self::IS_FILTERABLE_IN_GRID => 1,
                self::IS_SEARCHABLE_IN_GRID => 1
            ]
        ];

        foreach ($textFieldAttributes as $attributeCode => $config) {
            $this->addCustomerAttributeTextField($attributeCode, $config);
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
    protected function addCustomerAttributeTextField($attributeCode, $config)
    {
        $attribute = $this->customerAttributeResource->getIdByCode(\Magento\Customer\Api\CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER, $attributeCode);

        if (!$attribute) {
            try {
                $this->eavSetup->addAttribute(
                    \Magento\Customer\Api\CustomerMetadataInterface::ATTRIBUTE_SET_ID_CUSTOMER,
                    $attributeCode,
                    array_merge($config, [
                        'input' => 'text',
                        'system' => 0,
                        'user_defined' => 1,
                        'group' => 'General'
                    ])
                );
            } catch (\Exception $e) {
                $this->logger->error($e->getMessage());
            }
            $this->assignAttributeToForms($attributeCode);
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
