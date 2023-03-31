<?php

namespace Ipay88\Payment\Helper;

class Data extends \Magento\Framework\App\Helper\AbstractHelper
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    protected $magentoUrlBuilder;

    /**
     * @var \Magento\Framework\Encryption\EncryptorInterface
     */
    protected $magentoEncryptor;

    /**
     * @var \Ipay88\Payment\Gateway\Config\Config
     */
    protected $ipay88PaymentGatewayConfig;

    /**
     * @var \Ipay88\Payment\Helper\Config
     */
    protected $ipay88PaymentConfigHelper;

    /**
     * @var \Ipay88\Payment\Logger\Logger
     */
    protected $ipay88PaymentLogger;

    /**
     * @var array
     */

    /**
     * Data constructor.
     *
     * @param  \Magento\Framework\App\Helper\Context  $context
     * @param  \Ipay88\Payment\Gateway\Config\Config  $ipay88PaymentGatewayConfig
     */
    public function __construct(
        \Magento\Framework\App\Helper\Context $context,
        \Magento\Framework\Encryption\EncryptorInterface $magentoEncryptor,
        \Ipay88\Payment\Gateway\Config\Config $ipay88PaymentGatewayConfig,
        \Ipay88\Payment\Helper\Config $ipay88PaymentConfigHelper,
        \Ipay88\Payment\Logger\Logger $ipay88PaymentLogger
    ) {
        parent::__construct($context);

        $this->magentoUrlBuilder          = $context->getUrlBuilder();
        $this->magentoEncryptor           = $magentoEncryptor;
        $this->ipay88PaymentGatewayConfig = $ipay88PaymentGatewayConfig;
        $this->ipay88PaymentConfigHelper  = $ipay88PaymentConfigHelper;
        $this->ipay88PaymentLogger        = $ipay88PaymentLogger;
    }

    /**
     * @param  \Magento\Sales\Model\Order  $order
     *
     * @return array
     */
    public function generateRequestData(
        \Magento\Sales\Model\Order $order
    ): array {
        $amount = $this->ipay88PaymentConfigHelper->isTestingMode() ? 1 : $order->getGrandTotal();

        $productDescriptions = array_map(function (\Magento\Sales\Model\Order\Item $item) {
            return $item->getName();
        }, $order->getAllItems());

        $signature = $this->generateSignature([
            $this->magentoEncryptor->decrypt($this->ipay88PaymentGatewayConfig->getMerchantKey()), // merchant key
            $this->ipay88PaymentGatewayConfig->getMerchantCode(), // merchant code
            $order->getIncrementId(), //ref no
            number_format($amount, 2, '', ''), //amount
            $order->getOrderCurrencyCode(), //currency
            '', // xfield1
        ]);

        $paymentId = $order->getPayment()->getAdditionalInformation()['payment_id'] ?? '';

        return [
            'merchant_code'  => $this->ipay88PaymentGatewayConfig->getMerchantCode(),
            'payment_id'     => $paymentId,
            'ref_no'         => $order->getIncrementId(),
            'amount'         => number_format($amount, 2),
            'currency'       => $order->getOrderCurrencyCode(),
            'prod_desc'      => substr(implode(', ', $productDescriptions), 0, 90),
            'user_name'      => "{$order->getBillingAddress()->getFirstname()} {$order->getBillingAddress()->getLastname()}",
            'user_email'     => $order->getBillingAddress()->getEmail() ?: $order->getCustomerEmail(),
            'user_contact'   => $order->getBillingAddress()->getTelephone(),
            'remark'         => '',
            'lang'           => 'UTF-8',
            'signature_type' => 'SHA256',
            'signature'      => $signature,
            'response_url'   => $this->magentoUrlBuilder->getUrl('ipay88_payment/checkout/redirect'),
            'backend_url'    => $this->magentoUrlBuilder->getUrl('ipay88_payment/checkout/callback'),
            'appdeeplink'    => '',
            'Xfield1'        => '',
        ];
    }

    public function normalizeResponseData(array $params): array
    {
        return [
            'merchant_code' => $params['MerchantCode'] ?? '',
            'payment_id'    => $params['PaymentId'] ?? '',
            'ref_no'        => $params['RefNo'] ?? '',
            'amount'        => $params['Amount'] ?? '',
            'currency'      => $params['Currency'] ?? '',
            'remark'        => $params['Remark'] ?? '',
            'trans_id'      => $params['TransId'] ?? '',
            'auth_code'     => $params['AuthCode'] ?? '',
            'status'        => (int) ($params['Status'] ?? 0),
            'err_desc'      => $params['ErrDesc'] ?? '',
            'signature'     => $params['Signature'] ?? '',
            'cc_name'       => $params['CCName'] ?? '',
            'cc_no'         => $params['CCNo'] ?? '',
            's_bankname'    => $params['S_bankname'] ?? '',
            's_country'     => $params['S_country'] ?? '',
            'tran_date'     => $params['TranDate'] ?? '',
        ];
    }

    /**
     * @param  array  $response
     *
     * @return bool
     */
    public function isResponseSignatureExist(array $response): bool
    {
        return isset($response['signature']);
    }

    /**
     * @param  array  $response
     *
     * @return bool
     */
    public function isResponseSignatureMatched(array $response): bool
    {
        $signature = $this->generateSignature([
            $this->magentoEncryptor->decrypt($this->ipay88PaymentGatewayConfig->getMerchantKey()), // merchant key
            $this->ipay88PaymentGatewayConfig->getMerchantCode(), // merchant code
            $response['payment_id'], //payment id
            $response['ref_no'], //ref no
            preg_replace('/\D/', '', $response['amount']), //amount
            $response['currency'], //currency
            $response['status'], //status
        ]);

        return $signature === $response['signature'];
    }

    /**
     * @return bool
     */

    /**
     * Generature
     *
     * @param  array  $source
     *
     * @return false|string
     */
    public function generateSignature(array $source)
    {
        $hashed = hash('sha256', implode('', $source));

        $this->ipay88PaymentLogger->info('[signature]', [
            'source'    => $source,
            'signature' => $hashed,
        ]);

        return $hashed;
    }
}
