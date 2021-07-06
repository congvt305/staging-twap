<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Checkout\Model\Session;
use Magento\Checkout\Model\ShippingInformationManagement as MagentoShippingInformationManagement;
use Psr\Log\LoggerInterface;
use Eguana\RedInvoice\Model\RedInvoiceLogger;

/**
 * Get Red Invoice Data in checkout process
 * Class ShippingInformationManagement
 */
class ShippingInformationManagement
{
    /**
     * @var Session
     */
    private $checkoutSession;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var RedInvoiceLogger
     */
    private $redInvoicelogger;

    /**
     * ShippingInformationManagement constructor.
     * @param Session $checkoutSession
     * @param LoggerInterface $logger
     * @param RedInvoiceLogger $redInvoicelogger
     */
    public function __construct(
        Session $checkoutSession,
        LoggerInterface $logger,
        RedInvoiceLogger $redInvoicelogger
    ) {
        $this->checkoutSession = $checkoutSession;
        $this->logger = $logger;
        $this->redInvoicelogger = $redInvoicelogger;
    }

    /**
     * This function is used to save redinvoice data in checkout session
     * @param MagentoShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        MagentoShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $redInvoiceInfo = [];
        $extAttributes = $addressInformation->getExtensionAttributes();
        if (!isset($extAttributes)) {
            return;
        }
        $isApply = $extAttributes->getIsApply();
        $companyName = $extAttributes->getCompanyName();
        $taxCode = $extAttributes->getTaxCode();
        $state = $extAttributes->getState();
        $city = $extAttributes->getCity();
        $roadName = $extAttributes->getRoadName();

        try {
            $this->checkoutSession->setIsApply($isApply);
            $this->checkoutSession->setCompanyName($companyName);
            $this->checkoutSession->setTaxCode($taxCode);
            $this->checkoutSession->setState($state);
            $this->checkoutSession->setCity($city);
            $this->checkoutSession->setRoadName($roadName);

            $message = 'Red invoice info after setting into checkout session';
            $redInvoiceInfo = [
              'is_apply' => $isApply ? 'Yes' : 'No',
                'company_name' => $companyName,
                'tax_code' => $taxCode,
                'state' => $state,
                'city' => $city,
                'road_name' => $roadName
            ];
            $this->redInvoicelogger->logRedInvoiceInfo($message, $redInvoiceInfo);
        } catch (\Exception $exception) {
            $this->logger->error($exception->getMessage());
        }
    }
}
