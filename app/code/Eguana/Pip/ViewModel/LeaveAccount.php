<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 23/7/20
 * Time: 6:32 PM
 */
namespace Eguana\Pip\ViewModel;

use Eguana\Pip\Helper\Data;
use Magento\Framework\UrlInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * Class LeaveAccount
 *
 * Leave Account View Model
 */
class LeaveAccount implements ArgumentInterface
{
    /**
     * @var \Magento\Framework\UrlInterface
     */
    private $urlBuilder;

    /**
     * @var Data
     */
    private $helper;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder,
        Data $helper
    ) {
        $this->urlBuilder = $urlBuilder;
        $this->helper     = $helper;
    }

    /**
     * Get leave account link
     * @return string
     */
    public function getLink()
    {
        return $this->urlBuilder->getUrl('pip/account/leave');
    }

    /**
     * Get helper instance
     * @return Data
     */
    public function getHelper()
    {
        return $this->helper;
    }
}
