<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 4:07 PM
 */

namespace Eguana\GWLogistics\Model\Service;

use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterfaceFactory;
use Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface;
use Eguana\GWLogistics\Model\QuoteCvsLocation;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\MaskedQuoteIdToQuoteIdInterface;
use Magento\Quote\Model\Quote;
use Magento\Quote\Model\ShippingAddressManagementInterface;
use Psr\Log\LoggerInterface;

class SaveQuoteCvsLocation
{
    /**
     * @var QuoteCvsLocationInterfaceFactory
     */
    private $quoteCvsLocationInterfaceFactory;
    /**
     * @var QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var ShippingAddressManagementInterface
     */
    private $shippingAddressManagement;
    /**
     * @var MaskedQuoteIdToQuoteIdInterface
     */
    private $maskedQuoteIdToQuoteId;

    public function __construct(
        QuoteCvsLocationInterfaceFactory $quoteCvsLocationInterfaceFactory,
        QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        ShippingAddressManagementInterface $shippingAddressManagement,
        MaskedQuoteIdToQuoteIdInterface $maskedQuoteIdToQuoteId,
        LoggerInterface $logger
    ) {
        $this->quoteCvsLocationInterfaceFactory = $quoteCvsLocationInterfaceFactory;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->shippingAddressManagement = $shippingAddressManagement;
        $this->logger = $logger;
        $this->maskedQuoteIdToQuoteId = $maskedQuoteIdToQuoteId;
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
        $this->logger->info('gwlogistics | cvs store data for a map selection', $cvsStoreData);


        $quoteIdString = $cvsStoreData['MerchantTradeNo'] . $cvsStoreData['ExtraData'];
//        $quoteId = $this->getQuoteId($quoteIdString);
        $quoteId = $cvsStoreData['ExtraData'];
        try {

            /** @var Quote $quote */
            $quoteAddress = $this->shippingAddressManagement->get($quoteId);
//          //$this->setShippingAmount(0)->setBaseShippingAmount(0)->setShippingMethod('')->setShippingDescription('');
            $quoteAddress->setShippingMethod('gwlogistics_CVS');
            $quoteAddress->setSaveInAddressBook(0);
            $quoteAddress->setSameAsBilling(1);
            $quoteAddress->setCountryId('TW');
            $this->shippingAddressManagement->assign($quoteId, $quoteAddress);

        } catch (\Exception $e) {
            throw new \Exception(__('Quote Address id for Quote "%1" does not exist.', $quoteId), $e);
        }

        if ($this->findOldLocation($quoteAddress->getId())) {
            /** @var QuoteCvsLocation $cvsLocation */
            $cvsLocation = $this->findOldLocation($quoteAddress->getId());
        } else {
            $cvsLocation = $this->quoteCvsLocationInterfaceFactory->create();
        }

        $cvsLocation->setData('quote_id', $quoteId);
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

    private function findOldLocation($quoteAddressId)
    {
        return $this->quoteCvsLocationRepository->getByAddressId($quoteAddressId);
    }

    private function getQuoteId(string $quoteIdString)
    {
        $isCustomer = (substr($quoteIdString, 0, 2) === 'c_') ? true : false;
        $quoteIdString = substr($quoteIdString, 8);
        $quoteId = $isCustomer ? $quoteIdString : $this->maskedQuoteIdToQuoteId->execute($quoteIdString);
        return $quoteId;
    }

}
