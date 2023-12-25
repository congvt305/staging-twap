<?php


namespace Sapt\Sales\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Sales\Model\Order\Address\Renderer as AddressRenderer;

class OrderInfo implements ArgumentInterface
{
    /**
     * @var AddressRenderer
     */
    protected $renderer;

    public function __construct(
        AddressRenderer $renderer
    ) {
        $this->renderer = $renderer;
    }

    public function getFormattedAddress($shippingAddress)
    {
        return $this->renderer->format($shippingAddress, 'html');
    }
}
