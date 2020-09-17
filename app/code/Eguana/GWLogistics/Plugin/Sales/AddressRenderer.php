<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 8/8/20
 * Time: 7:06 AM
 */

namespace Eguana\GWLogistics\Plugin\Sales;


use Magento\Sales\Model\Order\Address;
use Magento\Sales\Model\Order\Address\Renderer;

class AddressRenderer
{
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;
    /**
     * @var \Eguana\GWLogistics\Helper\Data
     */
    private $data;

    public function __construct(
        \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository,
        \Eguana\GWLogistics\Helper\Data $data
    ) {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
        $this->data = $data;
    }

    /**
     * @param \Magento\Sales\Model\Order\Address\Renderer $subject
     * @param Address $address
     * @param string $type
     * @return array
     */
//    public function beforeFormat(\Magento\Sales\Model\Order\Address\Renderer $subject, Address $address, $type)
//    {
//        if ($address->getAddressType() === 'shipping' && $address->getCvsLocationId() && $this->data->isActive()) {
//            $address->setCity(null);
//            $address->setRegion(null);
//            $address->setPostcode(null);
//            $address->setStreet(null);
//        }
//        return [$address, $type];
//    }

    /**
     * @param \Magento\Sales\Model\Order\Address\Renderer $subject
     * @param $result
     * @param Address $address
     * @param string $type
     */
    public function afterFormat(\Magento\Sales\Model\Order\Address\Renderer $subject, $result, Address $address, $type)
    {
        if ($address->getAddressType() === 'shipping' && $address->getCvsLocationId() && $this->data->isActive()) {
            $cvsLocation = $this->quoteCvsLocationRepository->getById($address->getCvsLocationId());
            $name = $cvsLocation->getCvsStoreName();
            $type = __($cvsLocation->getLogisticsSubType());
            $result = '<strong>' . $name . '(' . $type . ')' . '</strong><br />' . $result . $cvsLocation->getCvsAddress() . ' ' . $cvsLocation->getCvsTelephone();
        }

        return $result;
    }
}
