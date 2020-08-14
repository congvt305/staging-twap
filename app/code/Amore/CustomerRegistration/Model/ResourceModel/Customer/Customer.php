<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 7. 24
 * Time: 오후 12:37
 */

namespace Amore\CustomerRegistration\Model\ResourceModel\Customer;

use Amore\CustomerRegistration\Model\POSLogger;
use Magento\Customer\Model\AccountConfirmation;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\InputException;
use Magento\Framework\Message\ManagerInterface;

/**
 * PLEASE ENTER ONE LINE SHORT DESCRIPTION OF CLASS
 * Class Customer
 * @package Amore\CustomerRegistration\Model\ResourceModel\Customer
 */
class Customer extends \Magento\Customer\Model\ResourceModel\Customer
{

    /**
     * @var \Magento\Eav\Api\AttributeRepositoryInterface
     */
    private $attributeRepository;
    /**
     * @var POSLogger
     */
    private $logger;

    /**
     * @var ManagerInterface
     */
    private $messageManager;

    public function __construct(
        \Amore\CustomerRegistration\Model\POSLogger $logger,
        \Magento\Eav\Api\AttributeRepositoryInterface $attributeRepository,
        ManagerInterface $messageManager,
        \Magento\Eav\Model\Entity\Context $context,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\Snapshot $entitySnapshot,
        \Magento\Framework\Model\ResourceModel\Db\VersionControl\RelationComposite $entityRelationComposite,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Framework\Validator\Factory $validatorFactory,
        \Magento\Framework\Stdlib\DateTime $dateTime,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        $data = [],
        AccountConfirmation $accountConfirmation = null
    ) {
        parent::__construct(
            $context,
            $entitySnapshot,
            $entityRelationComposite,
            $scopeConfig,
            $validatorFactory,
            $dateTime,
            $storeManager,
            $data,
            $accountConfirmation
        );

        $this->messageManager        = $messageManager;
        $this->attributeRepository   = $attributeRepository;
        $this->logger                = $logger;
    }

    /**
     * Add the uniquness validation on customer mobile number and integration number
     *
     * @param \Magento\Framework\DataObject|\Magento\Customer\Api\Data\CustomerInterface $customer
     *
     * @return $this
     * @throws AlreadyExistsException
     * @throws ValidatorException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    protected function _beforeSave(\Magento\Framework\DataObject $customer)
    {
        $mobileAttribute = null;
        $integrationNumberAttribute = null;

        try {
            $mobileAttribute = $this->attributeRepository->get('customer', 'mobile_number');
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }

        try {
            $integrationNumberAttribute = $this->attributeRepository->get('customer', 'integration_number');
        } catch (\Exception $e) {
            $this->logger->addExceptionMessage($e->getMessage());
        }

        if ($mobileAttribute) {
            $attributeId = $mobileAttribute->getAttributeId();
            $result = $this->attributeValueUseByOtherCustomer(
                $customer,
                $customer->getWebsiteId(),
                $customer->getMobileNumber(),
                $attributeId
            );
            if ($result) {
                // TODO Implement method (@Abbas Sir Please verify this functionality)
                //Edit by Arslan
                //Add message manager because the throw AlreadyExistsException was showing message from cache.
                //Now message manager add new message and InputException throw it to the same page
                $this->messageManager->addError(
                    __('A customer with the same mobile number already exists in an associated website.')
                );
                throw new InputException();
            }
        }

        if ($integrationNumberAttribute) {
            $attributeId = $integrationNumberAttribute->getAttributeId();
            $result = $this->attributeValueUseByOtherCustomer(
                $customer,
                $customer->getWebsiteId(),
                $customer->getIntegrationNumber(),
                $attributeId
            );
            if ($result) {
                $this->messageManager->addError(
                    __('A customer with the same integration/sequence number already exists in an associated website.')
                );
                throw new AlreadyExistsException(
                    __('A customer with the same integration/sequence number already exists in an associated website.')
                );
            }
        }

        return parent::_beforeSave($customer);
    }

    private function attributeValueUseByOtherCustomer($customer, $websiteId, $attributeValue, $attributeId)
    {

        $connection = $this->getConnection();
        $bind = ['attribute_id' => $attributeId, 'attribute_value' => $attributeValue];

        $select = $connection->select()->from(
            ['customer'=>$this->getEntityTable()],
            [$this->getEntityIdField()]
        );

        $select->joinLeft(
            ['cav' => 'customer_entity_varchar'],
            'cav.entity_id=customer.entity_id',
            []
        )
        ->where('cav.attribute_id = :attribute_id');

        $select->where('cav.value = :attribute_value');

        if ($customer->getSharingConfig()->isWebsiteScope()) {
            $bind['website_id'] = (int)$websiteId;
            $select->where('website_id = :website_id');
        }
        if ($customer->getId()) {
            $bind['entity_id'] = (int)$customer->getId();
            $select->where('customer.entity_id != :entity_id');
        }
        $result = $connection->fetchOne($select, $bind);
        return $result;
    }

}
