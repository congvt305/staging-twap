<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/9/20
 * Time: 6:11 AM
 */

namespace Eguana\GWLogistics\Plugin\Quote;


use Magento\Quote\Api\Data\AddressInterface;

class QuoteAddressAttributeLoad
{
    /**
     * @var \Magento\Quote\Api\Data\AddressExtensionFactory
     */
    private $extensionFactory;

    public function __construct(\Magento\Quote\Api\Data\AddressExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\Quote\Api\Data\AddressInterface $subject
     * @param $result
     */
    public function afterGetExtensionAttributes(\Magento\Quote\Api\Data\AddressInterface $entity, $extension)
    {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }
}
