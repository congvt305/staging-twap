<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created byPhpStorm
 * User:  Abbas
 * Date: 6/30/20
 * Time: 10:30 am
 */

namespace Eguana\OrderDeliveryMessage\Plugin\Checkout\Model;

use Magento\Checkout\Api\Data\ShippingInformationInterface;
use Magento\Framework\App\Request\DataPersistorInterface;

/**
 * Get Delivery Message in checkout process
 *
 * Class ShippingInformationManagement
 */
class ShippingInformationManagement
{
    /**
     * @var DataPersistorInterface
     */
    private $dataPersistor;

    /**
     * ShippingInformationManagement constructor.
     * @param DataPersistorInterface $dataPersistor
     */
    public function __construct(
        DataPersistorInterface $dataPersistor
    ) {

        $this->dataPersistor = $dataPersistor;
    }

    /**
     * SHORT DESCRIPTION
     * LONG DESCRIPTION LINE BY LINE
     * @param \Magento\Checkout\Model\ShippingInformationManagement $subject
     * @param $cartId
     * @param ShippingInformationInterface $addressInformation
     */
    public function beforeSaveAddressInformation(
        \Magento\Checkout\Model\ShippingInformationManagement $subject,
        $cartId,
        ShippingInformationInterface $addressInformation
    ) {
        $extAttributes = $addressInformation->getExtensionAttributes();
        if (!isset($extAttributes)) {
            return;
        }
        $deliveryMessage = $extAttributes->getDeliveryMessage();

        if (!isset($deliveryMessage)) {
            return;
        }

        $this->dataPersistor->set('delivery_message', $deliveryMessage);
    }

}
