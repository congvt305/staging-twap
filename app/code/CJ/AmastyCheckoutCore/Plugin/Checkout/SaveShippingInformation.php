<?php

namespace CJ\AmastyCheckoutCore\Plugin\Checkout;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\ShippingInformationManagement;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Quote\Model\QuoteRepository;
use Magento\Store\Model\StoreManagerInterface;

class SaveShippingInformation
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    protected $quoteRepository;

    /**
     * @param QuoteRepository $quoteRepository
     * @param StoreManagerInterface $storeManager
     */
    public function __construct(
        QuoteRepository $quoteRepository,
        StoreManagerInterface $storeManager
    )
    {
        $this->quoteRepository = $quoteRepository;
        $this->storeManager = $storeManager;
    }

    /**
     * @param ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     * @throws NoSuchEntityException
     */
    public function beforeSaveAddressInformation(
        ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    )
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $extAttributes = $addressInformation->getExtensionAttributes();
            $quote = $this->quoteRepository->getActive($cartId);

            $quote->setCountryPosCode($extAttributes->getCountryPosCode());
            $quote->setPackageOption($extAttributes->getPackageOption());

            $this->quoteRepository->save($quote);
        }
    }
}
