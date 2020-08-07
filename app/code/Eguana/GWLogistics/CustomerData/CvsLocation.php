<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/30/20
 * Time: 9:59 AM
 */

namespace Eguana\GWLogistics\CustomerData;

use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Customer\CustomerData\SectionSourceInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Checkout\Model\Session;
use Magento\Store\Model\StoreManagerInterface;

class CvsLocation implements SectionSourceInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var SessionManagerInterface
     */
    private $checkoutSession;
    /**
     * @var \Magento\Quote\Model\ShippingAddressManagement
     */
    private $shippingAddressManagement;
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $data;

    public function __construct(
        \Eguana\GWLogistics\Helper\Data $data,
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        \Magento\Quote\Model\ShippingAddressManagement $shippingAddressManagement,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
    ) {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->data = $data;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (NoSuchEntityException $exception) {
            $storeId = null;
        }
        $isEnabled = $this->data->isActive($storeId);
        $quoteId = $this->checkoutSession->getQuoteId();

        if (!$isEnabled || empty($quoteId)) {
            return [
                'cvs-location' => []
            ];
        }

        try {
            $shippingAddress = $this->shippingAddressManagement->get($quoteId);
            /** @var QuoteCvsLocationInterface $cvsLocation */
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($shippingAddress->getId());
            $cvsLocationData = [
                'CVSStoreName' => $cvsLocation->getCvsStoreName(),
                'CVSAddress' => $cvsLocation->getCvsAddress(),
            ];
        } catch (LocalizedException $e) {
            $cvsLocationData = [];
        }

        return [
            'cvs-location' => $cvsLocationData,
        ];
    }

}
