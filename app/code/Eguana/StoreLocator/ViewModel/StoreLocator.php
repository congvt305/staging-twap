<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 2/4/21
 * Time: 2:50 PM
 */
declare(strict_types=1);

namespace Eguana\StoreLocator\ViewModel;

use Eguana\StoreLocator\Helper\ConfigData;
use Magento\Framework\View\Element\Block\ArgumentInterface;

/**
 * View model class
 *
 * Class StoreLocator
 */
class StoreLocator implements ArgumentInterface
{
    /**
     * @var ConfigData
     */
    private $configHelper;

    /**
     * @param ConfigData $configHelper
     */
    public function __construct(
        ConfigData $configHelper
    ) {
        $this->configHelper = $configHelper;
    }

    /**
     * Get config zoom level
     *
     * @return int|mixed
     */
    public function getZoomLevel()
    {
        $zoom = $this->configHelper->getFrontMapZoom();
        return $zoom ? $zoom : 8;
    }

    /**
     * Get config North
     *
     * @return int|mixed
     */
    public function getMapNorth()
    {
        $mapNorth = $this->configHelper->getConfigValue('map_north');
        return $mapNorth ? $mapNorth : 0;
    }

    /**
     * Get config South
     *
     * @return int|mixed
     */
    public function getMapSouth()
    {
        $mapSouth = $this->configHelper->getConfigValue('map_south');
        return $mapSouth ? $mapSouth : 0;
    }

    /**
     * Get config West
     *
     * @return int|mixed
     */
    public function getMapWest()
    {
        $mapWest = $this->configHelper->getConfigValue('map_west');
        return $mapWest ? $mapWest : 0;
    }

    /**
     * Get config East
     *
     * @return int|mixed
     */
    public function getMapEast()
    {
        $mapEast = $this->configHelper->getConfigValue('map_east');
        return $mapEast ? $mapEast : 0;
    }
}
