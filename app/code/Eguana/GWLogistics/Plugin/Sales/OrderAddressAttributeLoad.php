<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/9/20
 * Time: 5:45 AM
 */

namespace Eguana\GWLogistics\Plugin\Sales;


use Magento\Sales\Api\Data\OrderAddressInterface;

class OrderAddressAttributeLoad
{
    /**
     * @var \Magento\Sales\Api\Data\OrderAddressExtensionFactory
     */
    private $extensionFactory;

    public function __construct(\Magento\Sales\Api\Data\OrderAddressExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $entity
     * @param $extension
     */
    public function afterGetExtensionAttributes(\Magento\Sales\Api\Data\OrderAddressInterface $entity, $extension)
    {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
