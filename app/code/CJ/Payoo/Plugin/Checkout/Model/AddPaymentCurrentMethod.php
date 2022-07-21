<?php
declare(strict_types=1);

namespace CJ\Payoo\Plugin\Checkout\Model;

use Amasty\CheckoutCore\Model\Config;
use Magento\Framework\Registry;
use Magento\Store\Model\StoreManagerInterface;

class AddPaymentCurrentMethod
{
    const PAYNOW_VISA = 'paynow-visa';

    const PAYNOW_WALLET = 'paynow-wallet';

    const STORE_CODE_VN_LANEIGE = 'vn_laneige';

    /**
     * @var Registry
     */
    private $registry;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @param StoreManagerInterface $storeManager
     * @param Registry $registry
     */
    public function __construct(
        StoreManagerInterface $storeManager,
        Registry $registry
    ) {
        $this->storeManager = $storeManager;
        $this->registry = $registry;
    }

    /**
     * save temporary current method in case one page checkout
     *
     * @param \Magento\Checkout\Api\PaymentInformationManagementInterface $subject
     * @param $cartId
     * @param \Magento\Quote\Api\Data\PaymentInterface $paymentMethod
     * @param \Magento\Quote\Api\Data\AddressInterface|null $billingAddress
     * @return void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function beforeSavePaymentInformationAndPlaceOrder(
        \Magento\Checkout\Api\PaymentInformationManagementInterface $subject,
        $cartId,
        \Magento\Quote\Api\Data\PaymentInterface $paymentMethod,
        \Magento\Quote\Api\Data\AddressInterface $billingAddress = null
    ) {
        if ($this->storeManager->getStore()->getCode() == self::STORE_CODE_VN_LANEIGE &&
            $paymentMethod->getMethod() == \Payoo\PayNow\Model\Ui\ConfigProvider::CODE
        ) {
            $extAttributes = $paymentMethod->getData('extension_attributes');
            if (!isset($extAttributes)) {
                return;
            }
            switch ($extAttributes->getCurrentMethod()) {
                case self::PAYNOW_VISA:
                    $methodTab = 'CC';
                    break;
                case self::PAYNOW_WALLET:
                    $methodTab = 'Payoo-account';
                    break;
                default:
                    $methodTab = 'Bank-account';
                    break;
            }
            $this->registry->register('method_tab', $methodTab);
        }
    }
}
