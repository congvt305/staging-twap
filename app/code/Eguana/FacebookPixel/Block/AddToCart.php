<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 9/2/21
 * Time: 4:14 AM
 */
namespace Eguana\FacebookPixel\Block;

use Eguana\FacebookPixel\Model\SessionFactory;
use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Block to provide Add to cart data
 *
 * Class AddToCart
 */
class AddToCart implements SectionSourceInterface
{
    /**
     * @var SessionFactory
     */
    private $fbPixelSession;

    /**
     * AddToCart constructor.
     * @param SessionFactory $fbPixelSession
     */
    public function __construct(
        SessionFactory $fbPixelSession
    ) {
        $this->fbPixelSession = $fbPixelSession;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        $data = [
            'events' => []
        ];
        if ($this->fbPixelSession->create()->hasAddToCart()) {
            $data['events'][] = [
                'eventName' => 'AddToCart',
                'eventAdditional' => $this->fbPixelSession->create()->getAddToCart()
            ];
        }
        return $data;
    }
}
