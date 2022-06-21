<?php

namespace Payoo\PayNow\Gateway\Request;

use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Payment\Gateway\Data\PaymentDataObjectInterface;
use Magento\Payment\Gateway\Request\BuilderInterface;
use Magento\Store\Model\ScopeInterface;
use Magento\Sales\Api\OrderRepositoryInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class RefundRequest implements BuilderInterface
{

    const CHECKSUM_KEY= 'payment/paynow/checksum_key';
    const REFUND_URL= 'payment/paynow/refund_url';
    const API_PASSWORD= 'payment/paynow/api_password';
    const API_SIGNATURE= 'payment/paynow/api_signature';
    const API_USERNAME= 'payment/paynow/api_username';

    /**
     * @var ScopeConfigInterface
     */
    private ScopeConfigInterface $config;

    /**
     * @var OrderRepositoryInterface
     */
    private OrderRepositoryInterface $orderRepository;

    /**
     * @var TimezoneInterface
     */
    private TimezoneInterface $localeDate;

    /**
     * @param TimezoneInterface $localeDate
     * @param OrderRepositoryInterface $orderRepository
     * @param ScopeConfigInterface $config
     */
    public function __construct(
        TimezoneInterface $localeDate,
        OrderRepositoryInterface $orderRepository,
        ScopeConfigInterface $config
    ) {
        $this->localeDate = $localeDate;
        $this->orderRepository = $orderRepository;
        $this->config = $config;
    }

    /**
     * @param array $buildSubject
     * @return array
     */
    public function build(array $buildSubject)
    {
        if (!isset($buildSubject['payment'])
            || !$buildSubject['payment'] instanceof PaymentDataObjectInterface
        ) {
            throw new \InvalidArgumentException('Payment data object should be provided');
        }

        $payment = $buildSubject['payment'];
        $amount = $buildSubject['amount'];
        $order = $payment->getOrder();

        $data = $this->buildData($order, $amount);

        return [
            'refund_url' => $this->getInformation(self::REFUND_URL, $order->getStoreId()),
            'apipassword' => $this->getInformation(self::API_PASSWORD, $order->getStoreId()),
            'apisignature' => $this->getInformation(self::API_SIGNATURE, $order->getStoreId()),
            'apiusername' => $this->getInformation(self::API_USERNAME, $order->getStoreId()),
            'RequestData' => $data['RequestData'],
            'Signature' => $data['Signature']
        ];
    }

    /**
     * @param $urlPath
     * @param $storeId
     * @return mixed
     */
    private function getInformation($urlPath, $storeId)
    {
        return $this->config->getValue(
            $urlPath,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * @param $order
     * @param $amount
     * @return array
     */
    private function buildData($order, $amount) {
        $orderRes = $this->orderRepository->get($order->getId());
        $invoice = $orderRes->getInvoiceCollection()->getFirstItem();
        $currentDateOrder = $this->getTimezoneStore($invoice->getCreatedAt(), $orderRes->getStore());
        $data = [
            'OrderNo' => $orderRes->getIncrementId(),
            'Money' => $amount,
            'Description' => 'Refund from GECP',
            'ActionType' => 2, // refund
            'PurchaseDate' => date('Ymd', strtotime($currentDateOrder))
        ];
        $checksumKey = nl2br($this->getInformation(self::CHECKSUM_KEY, $order->getStoreId()));
        $strData = json_encode($data);
        $checksum = hash('sha512',$checksumKey . $strData);
        return 	['RequestData' => $strData, 'Signature' => $checksum];
    }

    /**
     * @param $date
     * @param $store
     * @return string
     */
    private function getTimezoneStore($date, $store)
    {
        $timezone =  $this->localeDate->getConfigTimezone(
            ScopeInterface::SCOPE_STORE,
            $store->getCode()
        );

        return $this->localeDate->formatDateTime(
            $date,
            \IntlDateFormatter::MEDIUM,
            \IntlDateFormatter::MEDIUM,
            null,
            $timezone
        );
    }
}
