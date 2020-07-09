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

    public function __construct(
        StoreManagerInterface $storeManager,
        Session $checkoutSession,
        \Magento\Quote\Model\ShippingAddressManagement $shippingAddressManagement,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
    ) {
        $this->storeManager = $storeManager;
        $this->checkoutSession = $checkoutSession;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
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
//        $isEnabled = $this->config->isEnabled($storeId) && $this->config->isClickAndCollectEnabled($storeId); todo configuration
        $isEnabled = true;
        $quoteId = $this->checkoutSession->getQuoteId();

        if (!$isEnabled || empty($quoteId)) {
            return [
                'cvs-location' => [],
                'search-request' => []
            ];
        }

        try {
            $shippingAddress = $this->shippingAddressManagement->get($quoteId);
            /** @var QuoteCvsLocationInterface $cvsLocation */
            $cvsLocation = $this->quoteCvsLocationRepository->getByAddressId($shippingAddress->getId());
            $cvsLocationData = [
                'LogisticsSubType' => $cvsLocation->getLogisticsSubType(),
                'CVSStoreID' => $cvsLocation->getCvsStoreId(),
                'CVSStoreName' => $cvsLocation->getCvsStoreName(),
                'CVSAddress' => $cvsLocation->getCvsAddress(),
                'CVSTelephone' => $cvsLocation->getCvsTelephone(),
            ];
        } catch (LocalizedException $e) {
            $searchRequest = [];
            $cvsLocationData = [];
        }

        return [
            'cvs-location' => $cvsLocationData,
            'search-request' => ['UNIMART']
        ];
    }

}
