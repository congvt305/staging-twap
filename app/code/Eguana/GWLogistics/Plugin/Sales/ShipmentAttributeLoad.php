<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/8/20
 * Time: 4:47 PM
 */

namespace Eguana\GWLogistics\Plugin\Sales;


use Magento\Sales\Api\Data\ShipmentInterface;
use Magento\Sales\Api\Data\ShipmentExtensionInterface;
use Magento\Sales\Api\Data\ShipmentExtensionFactory;

class ShipmentAttributeLoad
{
    /**
     * @var ShipmentExtensionFactory
     */
    private $extensionFactory;

    public function __construct(ShipmentExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    public function afterGetExtensionAttributes(
        ShipmentInterface $entity,
        ShipmentExtensionInterface $extension = null
    ) {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }

}
