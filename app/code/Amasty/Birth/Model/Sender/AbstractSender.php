<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package Special Occasion Coupons for Magento 2
*/

namespace Amasty\Birth\Model\Sender;

use Amasty\Birth\Helper\Data;
use Amasty\Birth\Model\LogFactory;
use Amasty\Birth\Model\ResourceModel\Log\Collection;
use Amasty\Birth\Model\ResourceModel\Log\CollectionFactory;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\CustomerFactory;
use Magento\Customer\Model\ResourceModel\Customer\Collection as CustomerCollection;
use Magento\Customer\Model\ResourceModel\Customer\CollectionFactory as CustomerCollectionFactory;
use Magento\Framework\App\Area;
use Magento\Framework\App\ResourceConnection;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;

class AbstractSender
{
    /**
     * @var Data
     */
    protected $helper;

    /**
     * @var CustomerCollectionFactory
     */
    protected $collectionFactory;

    /**
     * @var CollectionFactory
     */
    protected $logCollectionFactory;

    /**
     * @var LogFactory
     */
    protected $logFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var TransportBuilder
     */
    protected $transportBuilder;

    /**
     * @var ManagerInterface
     */
    protected $messageManager;

    /**
     * @var CustomerFactory
     */
    protected $customerFactory;

    /**
     * @var ResourceConnection
     */
    protected $resource;

    public function __construct(
        Data $helper,
        CustomerCollectionFactory $collectionFactory,
        CollectionFactory $logCollectionFactory,
        LogFactory $logFactory,
        DateTime $date,
        TransportBuilder $transportBuilder,
        ManagerInterface $messageManager,
        CustomerFactory $customerFactory,
        ResourceConnection $resource
    ) {
        $this->helper = $helper;
        $this->collectionFactory = $collectionFactory;
        $this->logCollectionFactory = $logCollectionFactory;
        $this->logFactory = $logFactory;
        $this->date = $date;
        $this->transportBuilder = $transportBuilder;
        $this->messageManager = $messageManager;
        $this->customerFactory = $customerFactory;
        $this->resource = $resource;
    }

    /**
     * @return CustomerCollection
     */
    protected function _getCollection()
    {
        /** @var CustomerCollection $collection */
        $collection = $this->collectionFactory->create()
            ->addNameToSelect()
            ->addAttributeToSelect('email')
            ->setPageSize(300)
            ->setCurPage(1);

        return $collection;
    }

    /**
     * @param Customer $customer
     * @param string $type
     */
    protected function _emailToCustomer(Customer $customer, $type)
    {
        if (!$this->helper->getModuleConfig($type . '/enabled', $customer->getStoreId())) {
            return;
        }

        $groupId = $customer->getGroupId();
        $groups = $this->helper->getModuleConfig(
            $type . '/customer_group',
            $customer->getStoreId()
        );

        if (!empty($groups)) {
            $groups = explode(',', $groups);

            if (!in_array($groupId, $groups)) {
                return;
            }
        }

        /** @var Collection $logCollection */
        $logCollection = $this->logCollectionFactory->create()
            ->addFieldToFilter('type', $type)
            ->addFieldToFilter('customer_id', $customer->getId());

        if (in_array($type, ['birth', 'wishlist', 'activity'])) {
            $logCollection->addFieldToFilter('y', $this->date->date('Y'));
        }

        if ($logCollection->getSize() > 0) {
            return;
        }

        $this->_sendMailToCustomer($customer, $type);

        $logModel = $this->logFactory->create()
            ->setY($this->date->date('Y'))
            ->setType($type)
            ->setCustomerId($customer->getId())
            ->setSentDate($this->date->date('Y-m-d H:i:s'));

        $logModel->save();
    }

    /**
     * @param Customer $customer
     * @param string $mailType
     */
    protected function _sendMailToCustomer(Customer $customer, $mailType)
    {
        try {
            $storeId = $customer->getStoreId();
            $store = $customer->getStore();
            $templateId = $this->helper->getModuleConfig($mailType . '/template', $storeId);
            $data = [
                'website_name' => $store->getWebsite()->getName(),
                'group_name' => $store->getGroup()->getName(),
                'store_name' => $store->getName(),
                'coupon' => $this->helper->generateCoupon($mailType, $store, $customer->getEmail()),
                'coupon_days' => $this->helper->getModuleConfig($mailType . '/coupon_days', $storeId),
                'customer_name' => $customer->getName(),
            ];
            if ($mailType === "regbirth") {
                $year = $this->date->date('y', $this->date->date())
                    - $this->date->date('y', $this->date->timestamp($customer->getData('created_at')));

                $data['years_from_reg'] = $year;
            }

            $transport = $this->transportBuilder->setTemplateIdentifier(
                $templateId
            )->setTemplateOptions(
                ['area' => Area::AREA_FRONTEND, 'store' => $storeId]
            )->setTemplateVars(
                $data
            )->setFrom(
                $this->helper->getModuleConfig('general/identity', $storeId)
            )->addTo(
                $customer->getEmail(),
                $customer->getName()
            )->getTransport();

            $transport->sendMessage();
        } catch (\Exception $e) {
            $this->messageManager->addExceptionMessage($e, __('Can\'t send customer email'));
        }
    }
}
