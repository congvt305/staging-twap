<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: saba
 * Date: 6/25/20
 * Time: 1:31 PM
 */

namespace Eguana\EcommerceStatus\ViewModel;

use Magento\Framework\App\Request\Http;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Customer\Model\Session;
use Eguana\EcommerceStatus\Helper\Data;

/**
 * Hide mini cart
 *
 * Class Switcher
 * @package Eguana\EcommerceStatus\ViewModel
 */
class Switcher implements ArgumentInterface
{

    /**
     * @var Data
     */
    private $helperData;

    /**
     * Switcher constructor.
     * @param Data $helperData
     */
    public function __construct(
        Data $helperData
    ) {
        $this->helperData = $helperData;
    }

    /**
     * Hide mini cart if Ecommerce switcher is enable
     * @return bool
     */
    public function getEnableMinicart()
    {
        return $this->helperData->getECommerceStatus();
    }
}
