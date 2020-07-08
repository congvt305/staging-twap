<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 7:42 AM
 */

namespace Eguana\GWLogistics\ViewModel;


use Magento\Framework\View\Element\Block\ArgumentInterface;

class CvsAddress implements ArgumentInterface
{
    /**
     * @var \Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface
     */
    private $quoteCvsLocationRepository;

    public function __construct(\Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface $quoteCvsLocationRepository)
    {
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function getCvsStoreData($order)
    {
        $cvsLocationId = $order->getShippingAddress()->getData('cvs_location_id');
        $cvsLocation = null;
        if ($cvsLocationId) {
            $cvsLocation = $this->quoteCvsLocationRepository->getById($cvsLocationId);
        }
        return $cvsLocation;

    }

    /**
     * @param \Magento\Sales\Model\Order\Address|null $shippingAddress
     */
    public function limitAddress($shippingAddress)
    {
        $firstName = $shippingAddress->getFirstname();
        $lastName = $shippingAddress->getLastname();
        $telephone = $shippingAddress->getTelephone();
        $shippingAddress->unsetData();
        $shippingAddress->setFirstname($firstName);
        $shippingAddress->setLastname($lastName);
        $shippingAddress->setTelephone($telephone);
        return $shippingAddress;
    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function hasCvsLocation($order)
    {
        return $order->getShippingMethod() === 'gwlogistics_CVS'; //need to check if cvs location exists?
    }
//
//    private function getCvsLocation()
//    {
//
//    }


}
