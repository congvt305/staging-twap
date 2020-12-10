<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 18/11/20
 * Time: 12:30 PM
 */
namespace Eguana\SocialLogin\Setup\Patch\Data;

use Magento\Customer\Api\CustomerMetadataInterface;
use Magento\Customer\Model\ResourceModel\Attribute as CustomerAttributeResource;
use Magento\Eav\Model\Config as EavConfig;
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Setup\EavSetup;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Eav\Api\AttributeRepositoryInterface;
use Psr\Log\LoggerInterface;

class AddLineIdAttribute implements DataPatchInterface
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
                'code'=>'line_id',
                'label'=>'Line Id',
                'type' => 'varchar',
                'required' => 0,
                'sort_order' => 200
            ]
        ];

        $yesNoAttributes = [
            ['code'=>'line_message_agreement','label'=>'Line Messages Agreement',
                'sort_order' => 201, 'required'=>false]
        ];

        foreach ($textFieldAttributes as $textFieldAttribute) {

            $this->addCustomerAttributeTextFiled(
                $textFieldAttribute['code'],
                $textFieldAttribute['label'],
                $textFieldAttribute['type'],
                $textFieldAttribute['required'],
                $textFieldAttribute['sort_order']
            );
        }

        foreach ($yesNoAttributes as $yesNoAttribute) {
            $this->addCustomerAttributeBoolean(
                $yesNoAttribute['code'],
                $yesNoAttribute['label'],
                $yesNoAttribute['sort_order'],
                $yesNoAttribute['required']
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
     * @param string $required   required
     */
    private function addCustomerAttributeTextFiled($code, $label, $type, $required, $sortOrder)
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
    private function addCustomerAttributeBoolean($code, $label, $sortOrder, $required)
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
                        'input' => 'boolean',
                        'source' => Boolean::class,
                        'default' => Boolean::VALUE_NO,
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
        }
    }
}
