<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/17/20
 * Time: 10:50 AM
 */

namespace Eguana\GWLogistics\Plugin\Rma;

use Magento\Rma\Api\Data\TrackInterface;

class TrackAttributeLoad
{
    /**
     * @var \Magento\Rma\Api\Data\TrackExtensionFactory
     */
    private $extensionFactory;

    public function __construct(\Magento\Rma\Api\Data\TrackExtensionFactory $extensionFactory)
    {
        $this->extensionFactory = $extensionFactory;
    }

    /**
     * @param \Magento\Rma\Api\Data\TrackInterface $entity
     * @param $extension
     */
    public function afterGetExtensionAttributes(\Magento\Rma\Api\Data\TrackInterface $entity, $extension)
    {
        if ($extension === null) {
            $extension = $this->extensionFactory->create();
        }

        return $extension;
    }

}
