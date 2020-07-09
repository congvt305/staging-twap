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
    private $extenstionFactory;

    public function __construct(\Magento\Sales\Api\Data\OrderAddressExtensionFactory $extenstionFactory)
    {
        $this->extenstionFactory = $extenstionFactory;
    }

    /**
     * @param \Magento\Sales\Api\Data\OrderAddressInterface $subject
     * @param $result
     */
    public function afterGetExtensionAttributes(\Magento\Sales\Api\Data\OrderAddressInterface $entity, $extenstion)
    {
        if ($extenstion === null) {
            $extenstion = $this->extenstionFactory->create();
        }

        return $extenstion;
    }
}
