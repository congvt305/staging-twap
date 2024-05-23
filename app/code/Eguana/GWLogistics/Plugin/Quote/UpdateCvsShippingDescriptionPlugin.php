<?php
declare(strict_types=1);

namespace Eguana\GWLogistics\Plugin\Quote;

use Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface;
use Eguana\GWLogistics\Helper\Data;
use Psr\Log\LoggerInterface;
use Magento\Quote\Model\Quote\Address;

/**
 * Class UpdateCvsShippingDescriptionPlugin
 */
class UpdateCvsShippingDescriptionPlugin
{
    /**
     * List of store codes which apply new shipping description
     */
    const NEW_SHIPPING_DESC_STORES = [
        'tw_laneige',
        'default'
    ];

    /**
     * @var QuoteCvsLocationRepositoryInterface
     */
    private QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository;

    /**
     * @var Data
     */
    private Data $helper;

    /**
     * @var LoggerInterface
     */
    private LoggerInterface $logger;

    /**
     * @param QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository
     * @param Data $helper
     * @param LoggerInterface $logger
     */
    public function __construct(
        QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        Data $helper,
        LoggerInterface $logger
    ) {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->helper = $helper;
        $this->logger = $logger;
    }

    /**
     * @param Address\ToOrder $subject
     * @param Address $object
     * @param $data
     * @return array
     */
    public function beforeConvert(Address\ToOrder $subject, Address $object, $data = [])
    {
        if ($object->getShippingMethod() === 'gwlogistics_CVS' && $object->getCvsLocationId()) {
            try {
                $cvsLocation = $this->quoteCvsLocationRepository->getById((int)$object->getCvsLocationId());
                $shippingDescription = $this->getShippingDescription($object->getQuote(), $cvsLocation->getLogisticsSubType());
                if (!empty($shippingDescription)) {
                    $object->setShippingDescription($shippingDescription);
                }
            } catch (\Exception $e) {
                $this->logger->error('An issue happens when updating description of CVS method: ' . $e->getMessage());
            }
        }

        return [$object, $data];
    }

    /**
     * Get shipping description by logistics subtype code
     *
     * @param $quote
     * @param $logisticsSubType
     * @return string
     */
    protected function getShippingDescription($quote, $logisticsSubType)
    {
        $store = $quote->getStore();
        if (!empty($logisticsSubType) && in_array($store->getCode(), self::NEW_SHIPPING_DESC_STORES)) {
            return $this->helper->getShippingTitleByCode($logisticsSubType, $store->getWebsiteId());
        }

        return '';
    }
}
