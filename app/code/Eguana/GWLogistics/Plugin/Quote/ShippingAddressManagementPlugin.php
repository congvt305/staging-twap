<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 4:44 AM
 */

namespace Eguana\GWLogistics\Plugin\Quote;

use Magento\Framework\Exception\LocalizedException;
use Magento\Quote\Api\Data\AddressInterface;

class ShippingAddressManagementPlugin
{
    const NEW_THEME_TW_LNG_PATH = 'AmorePacific/sapt_tw_laneige';

    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Magento\Quote\Api\Data\AddressExtensionFactory
     */
    private $addressExtensionFactory;
    /**
     * @var \Psr\Log\LoggerInterface
     */
    private $logger;

    /**
     * @var \Magento\Framework\View\DesignInterface
     */
    private \Magento\Framework\View\DesignInterface $design;

    /**
     * SetCvsLocationPlugin constructor.
     * @param \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
     * @param \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory
     * @param \Psr\Log\LoggerInterface $logger
     */
    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Magento\Quote\Api\Data\AddressExtensionFactory $addressExtensionFactory,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\View\DesignInterface $design
    ) {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->addressExtensionFactory = $addressExtensionFactory;
        $this->logger = $logger;
        $this->design = $design;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAddressManagementInterface $subject
     * @param $result
     * @param int $cartId
     */
    public function afterGet(\Magento\Quote\Model\ShippingAddressManagementInterface $subject, \Magento\Quote\Api\Data\AddressInterface $result, $cartId)
    {
        if ($this->design->getDesignTheme()->getThemePath() != self::NEW_THEME_TW_LNG_PATH) {
            if ($result->getShippingMethod() == 'gwlogistics_CVS') {
                if ($result->getData('cvs_location_id')) {
                    $extensionAttributes = $result->getExtensionAttributes();
                    try {
                        $extensionAttributes->setCvsLocationId($result->getData('cvs_location_id'));
                    } catch (\Exception $e) {
                        $this->logger->error("Error when set CVS Location Id: " . $e->getMessage());
                        throw new LocalizedException(__('Cannot find the CVS store location. Please try to choose CVS store again if it still error, please contact our CS Center'));
                    }
                    $result->setExtensionAttributes($extensionAttributes);
                }
            }
        }

        return $result;
    }

    /**
     * @param \Magento\Quote\Model\ShippingAddressManagementInterface $subject
     * @param int $cartId
     * @param AddressInterface $address
     * @return array
     */
    public function beforeAssign(\Magento\Quote\Model\ShippingAddressManagementInterface $subject, $cartId, AddressInterface $address)
    {
        if ($this->design->getDesignTheme()->getThemePath() != self::NEW_THEME_TW_LNG_PATH) {
            if ($address->getData('limit_carrier') === 'gwlogistics') {
                $extensionAttributes = $address->getExtensionAttributes();
                try {
                    $cvsLocation = $this->quoteCvsLocationRepository->getByQuoteId($cartId);
                    if ($cvsLocation->getLocationId()) {
                        $extensionAttributes->setCvsLocationId($cvsLocation->getLocationId());
                        $address->setCvsLocationId($extensionAttributes->getCvsLocationId());
                    } else {
                        throw new LocalizedException(__('Cannot find the CVS store location. Please try to choose CVS store again if it still error, please contact our CS Center'));
                    }
                } catch (\Exception $e) {
                    $this->logger->error("Error when choose CVS Location Id: " . $e->getMessage());
                    throw new LocalizedException(__('Cannot find the CVS store location. Please try to choose CVS store again if it still error, please contact our CS Center'));
                }
                $address->setExtensionAttributes($extensionAttributes);
            }
        }
        return [$cartId, $address];
    }
}
