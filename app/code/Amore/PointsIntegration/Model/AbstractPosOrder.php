<?php

namespace Amore\PointsIntegration\Model;

use Amore\PointsIntegration\Model\Source\Config;
use Amore\StaffReferral\Api\Data\ReferralInformationInterface;
use Amore\StaffReferral\Helper\Config as ReferralConfig;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Sales\Api\Data\OrderInterface;
use Magento\Sales\Model\Order;
use Magento\Store\Model\ScopeInterface;

abstract class AbstractPosOrder
{
    const POS_ORDER_TYPE_ORDER = '000010';

    const POS_ORDER_TYPE_CANCEL = '000030';

    const POS_ORDER_TYPE_RETURN = '000020';

    protected $orderItemData = [];
    protected $itemsSubtotal = 0;
    protected $itemsGrandTotal = 0;
    protected $itemsDiscountAmount = 0;

    const SKU_PREFIX_XML_PATH = 'sap/mall_info/sku_prefix';

    /**
     * @var ReferralConfig
     */
    private $referralConfig;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \CJ\Middleware\Model\Data
     */
    private $orderData;

    /**
     * @param ReferralConfig $referralConfig
     * @param CustomerRepositoryInterface $customerRepository
     * @param Config $config
     * @param \CJ\Middleware\Model\Data $orderData
     */
    public function __construct(
        ReferralConfig $referralConfig,
        CustomerRepositoryInterface $customerRepository,
        Config $config,
        \CJ\Middleware\Model\Data $orderData
    ) {
        $this->referralConfig = $referralConfig;
        $this->customerRepository = $customerRepository;
        $this->config = $config;
        $this->orderData = $orderData;
    }

    /**
     * Get customer
     *
     * @param $customerId
     * @return \Magento\Customer\Api\Data\CustomerInterface
     * @throws NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    protected function getCustomer($customerId)
    {
        return $this->customerRepository->getById($customerId);
    }


    /**
     * Get BA referral code
     *
     * @param Order $order
     * @param int $websiteId
     * @return float|mixed|string|null
     */
    protected function getReferralBACode($order, $websiteId)
    {
        if ($order instanceof OrderInterface) {
            if ($order->getData(ReferralInformationInterface::REFERRAL_BA_CODE_KEY)) {
                return $order->getData(ReferralInformationInterface::REFERRAL_BA_CODE_KEY);
            }
            return $this->referralConfig->getDefaultBcReferralCode($websiteId);
        }

        return '';
    }

    /**
     * Get friend referral code
     *
     * @param Order $order
     * @return float|mixed|string|null
     */
    protected function getFriendReferralCode($order)
    {
        if ($order instanceof OrderInterface) {
            if ($order->getData(ReferralInformationInterface::REFERRAL_FF_CODE_KEY)) {
                return $order->getData(ReferralInformationInterface::REFERRAL_FF_CODE_KEY);
            }
        }

        return '';
    }


    /**
     * Get sku prefix
     *
     * @param $storeId
     * @return mixed
     */
    protected function getSKUPrefix($storeId)
    {
        return $this->config->getValue(
            self::SKU_PREFIX_XML_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
    }

    /**
     * Correct price POS order item data
     *
     * @param $orderSubtotal
     * @param $orderDiscountAmount
     * @param $orderGrandTotal
     * @param $isDecimalFormat
     * @return void
     */
    protected function correctPricePOSOrderItemData($orderSubtotal, $orderDiscountAmount, $orderGrandTotal, $isDecimalFormat)
    {
        $this->orderItemData = $this->orderData->priceCorrector($orderGrandTotal, $this->itemsGrandTotal, $this->orderItemData, 'salAmt', $isDecimalFormat);
        $this->orderItemData = $this->orderData->priceCorrector($orderDiscountAmount, $this->itemsDiscountAmount, $this->orderItemData, 'dcAmt', $isDecimalFormat);
        $this->orderItemData = $this->orderData->priceCorrector($orderSubtotal, $this->itemsSubtotal, $this->orderItemData, 'netSalAmt', $isDecimalFormat);

        if ($isDecimalFormat) {
            $listToFormat = ['salAmt', 'dcAmt', 'netSalAmt', 'pointAccount', 'price'];

            foreach ($listToFormat as $field) {
                foreach ($this->orderItemData as $key => $value) {
                    if (isset($value[$field]) && (is_float($value[$field]) || is_int($value[$field]))) {
                        $this->orderItemData[$key][$field] = $this->orderData->formatPrice($value[$field], $isDecimalFormat);
                    }
                }
            }
        }
    }

    /**
     * Reset item data after send every order
     *
     * @return void
     */
    protected function resetData() {
        $this->orderItemData = [];
        $this->itemsSubtotal = 0;
        $this->itemsDiscountAmount = 0;
        $this->itemsGrandTotal = 0;
    }
}
