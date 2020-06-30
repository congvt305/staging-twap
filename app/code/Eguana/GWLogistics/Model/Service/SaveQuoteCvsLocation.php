<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 4:07 PM
 */

namespace Eguana\GWLogistics\Model\Service;


use Eguana\GWLogistics\Model\QuoteCvsLocation;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class SaveQuoteCvsLocation
{
    /**
     * @var \Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterfaceFactory
     */
    private $quoteCvsLocationInterfaceFactory;
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;
    /**
     * @var \Magento\Quote\Model\ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;
    /**
     * @var \Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation
     */
    private $quoteCvsLocationResource;

    public function __construct
    (
        \Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterfaceFactory $quoteCvsLocationInterfaceFactory,
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation $quoteCvsLocationResource,
        \Magento\Quote\Model\ShippingAddressManagementInterface $shippingAddressManagement,
        \Psr\Log\LoggerInterface $logger
    ) {
        $this->quoteCvsLocationInterfaceFactory = $quoteCvsLocationInterfaceFactory;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->logger = $logger;

        $this->quoteCvsLocationResource = $quoteCvsLocationResource;
    }

    /*
     * cvsStoreData:  {
    "MerchantID":"2000132",
    "MerchantTradeNo":"1592515364346",
    "LogisticsSubType":"UNIMART",
    "CVSStoreID":"991182",
    "CVSStoreName":"馥樺門市",
    "CVSAddress":"台北市南港區三重路23號1樓",
    "CVSTelephone":"",
    "CVSOutSide":"0",
    "ExtraData":"" //quote address id or quote id
    }
    */
    public function process(array $cvsStoreData)
    {
        $quoteId = (int)$cvsStoreData['ExtraData'];
        try {
            /** @var \Magento\Quote\Model\Quote $quote */
            $quoteAddress = $this->shippingAddressManagement->get($quoteId);
        } catch (NoSuchEntityException $e) {
            throw new NoSuchEntityException(__('Quote Address id for Quote "%1" does not exist.', $quoteId), $e);

        }

        if($this->findOldLocation($quoteAddress->getId())) {
            /** @var QuoteCvsLocation $cvsLocation */
            $cvsLocation = $this->findOldLocation($quoteAddress->getId());
        } else {
            $cvsLocation = $this->quoteCvsLocationInterfaceFactory->create();
        }

        $cvsLocation->setData('quote_address_id', $quoteAddress->getId());
        $cvsLocation->setData('merchant_trade_no', $cvsStoreData['MerchantTradeNo']);
        $cvsLocation->setData('logistics_sub_type', $cvsStoreData['LogisticsSubType']);
        $cvsLocation->setData('cvs_store_id', $cvsStoreData['CVSStoreID']);
        $cvsLocation->setData('cvs_store_name', $cvsStoreData['CVSStoreName']);
        $cvsLocation->setData('cvs_address', $cvsStoreData['CVSAddress']);
        $cvsLocation->setData('cvs_telephone', $cvsStoreData['CVSTelephone']);
        $cvsLocation->setData('cvs_outside', $cvsStoreData['CVSOutSide']);
        $cvsLocation->setData('extra_data', $cvsStoreData['ExtraData']);
        $cvsLocation->setData('is_selected', false);

        try {
            $this->quoteCvsLocationRepository->save($cvsLocation);
            return $cvsLocation;
        } catch (CouldNotSaveException $e) {
            $this->logger->critical($e->getMessage());
            return false;
        }
    }

    private function findOldLocation($quoteAddressId) {
        return $this->quoteCvsLocationRepository->getByAddressId($quoteAddressId);
    }

}
