<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/9/20
 * Time: 8:01 AM
 */

namespace Eguana\GWLogistics\Controller\Test;


use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;

class ExtensionAttr extends \Magento\Framework\App\Action\Action
{

    /**
     * @var \Magento\Sales\Api\OrderAddressRepositoryInterface
     */
    private $addressRepository;
    /**
     * @var \Magento\Sales\Api\ShipmentRepositoryInterface
     */
    private $shipmentRepository;
    /**
     * @var \Eguana\GWLogistics\Model\QuoteCvsLocationRepository
     */
    private $quoteCvsLocationRepository;

    public function __construct(
        \Magento\Sales\Api\OrderAddressRepositoryInterface $addressRepository,
        \Magento\Sales\Api\ShipmentRepositoryInterface $shipmentRepository,
        \Eguana\GWLogistics\Model\QuoteCvsLocationRepository $quoteCvsLocationRepository,
        Context $context
    ) {
        parent::__construct($context);
        $this->addressRepository = $addressRepository;
        $this->shipmentRepository = $shipmentRepository;
        $this->quoteCvsLocationRepository = $quoteCvsLocationRepository;
    }

    /**
     * Execute action based on request and return result
     *
     * QuoteAddress, OrderShipment repository get, getList test
     *
     * @return \Magento\Framework\Controller\ResultInterface|ResponseInterface
     * @throws \Magento\Framework\Exception\NotFoundException
     */
    public function execute()
    {
        $orderAddress = $this->addressRepository->get(211); //address id 211, 70
        $cvsLocationId = $orderAddress->getExtensionAttributes()->getCvsLocationId();
        $cvsStoreData = $this->quoteCvsLocationRepository->getById($cvsLocationId);

        $shipment = $this->shipmentRepository->get(52); //shipment id 52, 1624448
        $allpayId = $shipment->getExtensionAttributes()->getAllPayLogisticsId();


        echo('allPayId : ' . $allpayId);
        echo('<br/>');
        echo('<br/>');

        echo('cvs_location_id in order address table: ' . $cvsLocationId);
        echo('<br/>');
        echo('type: ' . $cvsStoreData->getLogisticsSubType());
        echo('<br/>');
        echo('name: ' . $cvsStoreData->getCvsStoreName());
        echo('<br/>');
        echo('address: ' . $cvsStoreData->getCvsAddress());
        echo('<br/>');
        echo('telephone: ' . $cvsStoreData->getCvsTelephone());
    }
}
