<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 4/11/20
 * Time: 3:54 PM
 */
declare(strict_types=1);

namespace Eguana\EventReservation\ViewModel\Counter;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Framework\UrlInterface;

/**
 * Class CounterModel
 *
 * Get counter urls
 */
class CounterModel implements ArgumentInterface
{
    /**
     * @var UrlInterface
     */
    private $urlBuilder;

    /**
     * @param UrlInterface $urlBuilder
     */
    public function __construct(
        UrlInterface $urlBuilder
    ) {
        $this->urlBuilder = $urlBuilder;
    }

    /**
     * Get counter url
     *
     * @return string
     */
    public function getCounterUrl() : string
    {
        return $this->urlBuilder->getUrl('event/counter/data', ['_secure' => true]);
    }

    /**
     * Get counter save url
     *
     * @return string
     */
    public function getCounterSaveUrl() : string
    {
        return $this->urlBuilder->getUrl('event/counter/save', ['_secure' => true]);
    }
}
