<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 11/25/20
 * Time: 4:56 PM
 */
declare(strict_types=1);

namespace Eguana\GWLogistics\Model\Ui;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\DB\Select;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;

class ConfigProvider implements ConfigProviderInterface
{
    const CODE = 'gwlogistics';

    const CUSTOMER_ID = 'customer_id';

    const ENTITY_ID = 'entity_id';

    const SHIPPING_METHOD = 'shipping_method';

    const CVS_SHIPPING_METHOD = 'gwlogistics_CVS';
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $helper;

    /**
     * @var Session
     */
    private $customerSession;

    /**
     * @var CollectionFactory
     */
    private CollectionFactory $orderCollectionFactory;

    /**
     * @param \Eguana\GWLogistics\Helper\Data $helper
     * @param Session $customerSession
     * @param CollectionFactory $orderCollectionFactory
     */
    public function __construct(
        \Eguana\GWLogistics\Helper\Data $helper,
        Session $customerSession,
        CollectionFactory $orderCollectionFactory
    ) {
        $this->helper = $helper;
        $this->customerSession = $customerSession;
        $this->orderCollectionFactory = $orderCollectionFactory;
    }

    public function getConfig()
    {
        $isActive = $this->helper->isActive();
        if (!$isActive) {
            return [];
        }
        $firstName = '';
        $lastName = '';
        $mobileNumber = '';
        if ($this->customerSession->isLoggedIn()) {
            $orderCollection = $this->orderCollectionFactory->create();
            $orderCollection->getSelect()->reset(Select::COLUMNS);
            $orderData = $orderCollection->join(
                ['soa' => 'sales_order_address'],
                'soa.parent_id = main_table.entity_id',
                ['lastname','firstname','telephone'])
                ->addFieldToFilter(self::CUSTOMER_ID, $this->customerSession->getCustomerId())
                ->addFieldToFilter(self::SHIPPING_METHOD, self::CVS_SHIPPING_METHOD)
                ->setOrder('main_table.' . self::ENTITY_ID, 'DESC')
                ->getFirstItem();
            $firstName = $orderData->getFirstname();
            $lastName = $orderData->getLastname();
            $mobileNumber = $orderData->getTelephone();
        }


        return [
                self::CODE => [
                    'isActive' => $isActive,
                    'shipping_message' => $this->helper->getCarrierShippingMessage(),
                    'guest_cvs_shipping_method_enabled' => $this->helper->isGuestCVSShippingMethodEnabled(),
                    'cvs_first_name' => $firstName,
                    'cvs_last_name' => $lastName,
                    'mobile_number' => $mobileNumber
                ],
        ];
    }
}
