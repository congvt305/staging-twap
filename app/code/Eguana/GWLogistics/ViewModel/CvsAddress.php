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

    public function getCvsAddress()
    {

    }

    /**
     * @param \Magento\Sales\Model\Order $order
     */
    public function hasCvsLocation($order)
    {
        return $order->getShippingMethod() === 'gwlogistics_CVS'; //need to check if cvs location exists?
    }


}
