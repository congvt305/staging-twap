<?php

declare(strict_types=1);

namespace CJ\NinjaVanShipping\Model;

use CJ\NinjaVanShipping\Api\GetTrackUrlByOrderInterface;
use CJ\NinjaVanShipping\Helper\Data as NinjaVanShippingHelper;

class GetTrackUrlByOrder implements GetTrackUrlByOrderInterface
{
    const MY_PREFIX = 'NVSG';

    /**
     * @var NinjaVanShippingHelper
     */
    private $ninjaVanShippingHelper;

    /**
     * @param NinjaVanShippingHelper $ninjaVanShippingHelper
     */
    public function __construct(NinjaVanShippingHelper $ninjaVanShippingHelper)
    {
        $this->ninjaVanShippingHelper = $ninjaVanShippingHelper;
    }

    /**
     * {@inheritDoc}
     */
    public function execute($order): string
    {
        $trackUrl = '';
        foreach ($order->getShipmentsCollection() as $shipment) {
            foreach ($shipment->getTracksCollection() as $track) {
                $trackNumber = $track->getNumber();
                $trackNumberPrefix = substr($trackNumber, 0, 4);
                if ($trackNumberPrefix != self::MY_PREFIX) {
                    continue;
                }

                $ninjaVanTrackUrl = $this->ninjaVanShippingHelper->getNinjaVanTrackUrl();
                return $ninjaVanTrackUrl . $track->getTrackNumber();
            }
        }

        return $trackUrl;
    }
}
