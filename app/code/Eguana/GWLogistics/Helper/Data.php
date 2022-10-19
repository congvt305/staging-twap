<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/20/20
 * Time: 7:51 PM
 */

namespace Eguana\GWLogistics\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Framework\App\Helper\Context;
use Magento\Rma\Api\RmaRepositoryInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

class Data extends AbstractHelper
{
    const XML_PATH_ACTIVE = 'carriers/gwlogistics/active';
    const XML_PATH_TITLE = 'carriers/gwlogistics/title';
    const XML_PATH_NAME = 'carriers/gwlogistics/name';
    const XML_PATH_SHIPPING_PRICE = 'carriers/gwlogistics/shipping_price';
    const XML_PATH_SHIPPING_MESSAGE = 'carriers/gwlogistics/shipping_message';
    const XML_PATH_SENDER_NAME = 'carriers/gwlogistics/sender_name';
    const XML_PATH_SENDER_PHONE = 'carriers/gwlogistics/sender_phone';
    const XML_PATH_SENDER_CELL_PHONE = 'carriers/gwlogistics/sender_cell_phone';
    const XML_PATH_SENDER_EMAIL = 'carriers/gwlogistics/sender_email';

    const XML_PATH_MERCHANT_ID = 'carriers/gwlogistics/merchant_id';
    const XML_PATH_PLATFORM_ID = 'carriers/gwlogistics/platform_id';
    const XML_PATH_HASH_KEY = 'carriers/gwlogistics/hash_key';
    const XML_PATH_HASH_IV = 'carriers/gwlogistics/hash_iv';

    const XML_PATH_MAP_URL = 'carriers/gwlogistics/map_url';
    const XML_PATH_CREATE_LOGISTICS_ORDER_URL = 'carriers/gwlogistics/create_logistics_order_url';
    const XML_PATH_LOGISTICS_QUERY_URL = 'carriers/gwlogistics/logistics_order_query_url';
    const XML_PATH_FAMI_REVERSE_LOGISTICS_URL = 'carriers/gwlogistics/fami_reverse_logistics_order_url';
    const XML_PATH_UNIMART_REVERSE_LOGISTICS_URL = 'carriers/gwlogistics/unimart_reverse_logistics_order_url';

    const XML_PATH_MODE = 'carriers/gwlogistics/mode';
    const XML_PATH_SERVER_TYPE = 'carriers/gwlogistics/server_type';
    const XML_PATH_SEND_SMS_ACTIVE = 'carriers/gwlogistics/send_sms_active';
    const XML_PATH_SEND_SMS_PREFIX = 'carriers/gwlogistics/send_sms_prefix';
    const XML_PATH_MESSAGE_TEMPLATE = 'carriers/gwlogistics/message_template';
    const XML_PATH_MESSAGE_GOODSNAME_PREFIX = 'carriers/gwlogistics/goodsname_prefix';

    const XML_PATH_ENABLE_CRON = 'eguana_gwlogistics/cron_settings/enable_cron';
    const XML_PATH_ORDER_STATUS_TO_CREATE_SHIPMENT = 'eguana_gwlogistics/cron_settings/order_status_to_create_shipment';
    const XML_PATH_LAST_ORDER_ID = 'eguana_gwlogistics/cron_settings/last_order_id';
    const XML_PATH_CREATE_SHIPMENT_CRON_SCHEDULE = 'eguana_gwlogistics/cron_settings/create_shipment_cron_schedule';
    const XML_PATH_GUEST_CVS_SHIPPING_METHOD_ENABLED = 'eguana_gwlogistics/general/guest_cvs_shipping_method_enabled';

    private $productionMode = null;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayCheckMacValue
     */
    private $ecpayCheckMacValue;
    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    private $encryptor;
    /**
     * @var \Eguana\GWLogistics\Model\Lib\EcpayLogistics
     */
    private $ecpayLogistics;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var OrderRepositoryInterface
     */
    private $orderRepository;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;

    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Encryption\EncryptorInterface $encryptor,
        \Eguana\GWLogistics\Model\Lib\EcpayCheckMacValue $ecpayCheckMacValue,
        \Eguana\GWLogistics\Model\Lib\EcpayLogistics $ecpayLogistics,
        OrderRepositoryInterface $orderRepository,
        RmaRepositoryInterface $rmaRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->ecpayCheckMacValue = $ecpayCheckMacValue;
        $this->encryptor = $encryptor;
        $this->ecpayLogistics = $ecpayLogistics;
        $this->logger = $logger;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->orderRepository = $orderRepository;
        $this->rmaRepository = $rmaRepository;
    }

    public function isActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCarrierTitle($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_TITLE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getCarrierShippingMessage($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SHIPPING_MESSAGE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
    public function getMapServerReplyUrl($storeId = null)
    {
        return $this->_getUrl('eguana_gwlogistics/SelectedCvsNotify', ['_secure' => true]);
    }

    public function getCreateShipmentReplyUrl($storeId = null)
    {
        $storeBaseUrl = $this->scopeConfig->getValue(
            'web/secure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $storeBaseUrl . 'eguana_gwlogistics/OrderStatusNotify';
    }

    public function getReverseLogisticsOrderReplyUrl($storeId = null)
    {
        $storeBaseUrl = $this->scopeConfig->getValue(
            'web/secure/base_url',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
        return $storeBaseUrl . 'eguana_gwlogistics/ReverseOrderStatusNotify';
    }

    public function getSenderName($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_NAME,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSenderPhone($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_PHONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSenderCellPhone($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SENDER_CELL_PHONE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getPlatformId($storeId = null)
    {
        $suffix = $this->getMode($storeId) === '1' ? '' : '_sandbox';
        return $this->scopeConfig->getValue(
            self::XML_PATH_PLATFORM_ID . $suffix,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getHashKey($storeId = null)
    {
        if ($this->getMode($storeId) === '1') {
            return $this->encryptor->decrypt(
                $this->scopeConfig->getValue(
                    self::XML_PATH_HASH_KEY,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            );
        }
        return $this->scopeConfig->getValue(
            self::XML_PATH_HASH_KEY . '_sandbox',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getHashIv($storeId = null)
    {
        if ($this->getMode($storeId) === '1') {
            return $this->encryptor->decrypt(
                $this->scopeConfig->getValue(
                    self::XML_PATH_HASH_IV,
                    \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                    $storeId
                )
            );
        }
        return $this->scopeConfig->getValue(
            self::XML_PATH_HASH_IV . '_sandbox',
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE
        );
    }

    public function getMerchantId($storeId = null)
    {
        $suffix = $this->getMode($storeId) === '1' ? '' : '_sandbox';
        $this->logger->info('gwlogistics | data getMerchantId suffix argument | '. $suffix);
        return $this->scopeConfig->getValue(
            self::XML_PATH_MERCHANT_ID . $suffix,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMapUrl($storeId = null)
    {
        $suffix = $this->getMode($storeId) === '1' ? '' : '_sandbox';
        return $this->scopeConfig->getValue(
            self::XML_PATH_MAP_URL . $suffix,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMode($storeId = null)
    {
        if (!$this->productionMode) {
            $this->productionMode = $this->scopeConfig->getValue(
                self::XML_PATH_MODE,
                \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
                $storeId
            );
        }
        return $this->productionMode;
    }

    public function getServerType($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SERVER_TYPE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function validateCheckMackValue(array $params, $storeId = null): bool
    {
        $hashKey = $this->getHashKey($storeId);
        $hashIv = $this->getHashIv($storeId);
        try {
            $this->ecpayLogistics->HashKey = $hashKey;
            $this->ecpayLogistics->HashIV = $hashIv;
            $this->ecpayLogistics->CheckOutFeedback($params);
            return true;
        } catch (\Exception $e) {
            return true; //todo fix after verify reason, found that special character is the cause.
        }
    }

    public function getSendSmsActive($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEND_SMS_ACTIVE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getSmsPrefix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_SEND_SMS_PREFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getMessageTemplate($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE_TEMPLATE,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    public function getGoodsNamePrefix($storeId = null)
    {
        return $this->scopeConfig->getValue(
            self::XML_PATH_MESSAGE_GOODSNAME_PREFIX,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function hasCvsLocation($order)
    {
        return $order->getShippingMethod() === 'gwlogistics_CVS' && $this->getCvsStoreData($order);
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function getCvsStoreData($order)
    {
        $cvsLocationId = $order->getShippingAddress()->getData('cvs_location_id');
        $cvsLocation = null;
        if ($cvsLocationId) {
            $cvsLocation = $this->quoteCvsLocationRepository->getById($cvsLocationId);
        }
        return $cvsLocation;
    }

    public function getMyOrderTrackingUrl($orderId)
    {
        $order = $this->orderRepository->get($orderId);
        $customerId = $order->getCustomerId();
        $url = 'sales/order/shipment/order_id/' . $orderId;
        return $customerId ? $this->_urlBuilder->getUrl($url) : null;
    }

    public function getMyRmaTrackingUrl($rmaId)
    {
        $rma = $this->rmaRepository->get($rmaId);
        $customerId = $rma->getCustomerId();
        $url = 'sales/order/shipment/order_id/' . $rmaId;
        return $customerId ? $this->_urlBuilder->getUrl($url) : null;
    }

    public function getOrderStatusToCreateShipment()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ORDER_STATUS_TO_CREATE_SHIPMENT);
    }

    public function getCreateShipmentCronSchedule()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_CREATE_SHIPMENT_CRON_SCHEDULE);
    }

    public function isCronEnabled()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_ENABLE_CRON);
    }

    public function getLastOrderId()
    {
        return $this->scopeConfig->getValue(self::XML_PATH_LAST_ORDER_ID);
    }

    /**
     * @param $storeId
     * @return bool
     */
    public function isGuestCVSShippingMethodEnabled($storeId = null)
    {
        return (bool) $this->scopeConfig->getValue(
            self::XML_PATH_GUEST_CVS_SHIPPING_METHOD_ENABLED,
            \Magento\Store\Model\ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }
}

