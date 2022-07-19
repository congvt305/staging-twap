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
use Magento\Eav\Model\Entity\Attribute\Source\Boolean;
use Magento\Eav\Model\Entity\Attribute\ScopedAttributeInterface;

/**
 * This class is responsible for add attribute in customer
 *
 * Class AddCustomerCustomAttributeTermPersonalData
 */
class AddCustomerCustomAttributeTermPersonalData implements DataPatchInterface
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
        $yesNoAttributes = [
            ['code'=>'term_personal_data','label'=>'Term Personal Data',
                'sort_order' => 143, 'required'=>false]
        ];

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
        /*$this->eavSetup->removeAttribute(
            CustomerMetadataInterface::ENTITY_TYPE_CUSTOMER,
            $code
        );*/
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
                        'global' => ScopedAttributeInterface::SCOPE_GLOBAL,
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
