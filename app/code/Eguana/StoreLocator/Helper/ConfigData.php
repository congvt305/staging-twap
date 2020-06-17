<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 06/17/20
 * Time: 12:00 PM
 */
namespace Eguana\StoreLocator\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper for getting config fields
 *
 * Class ConfigData
 *  Eguana\StoreLocator\Helper
 */
class ConfigData extends AbstractHelper
{
    const GENERAL_STORES = 'stores_board/general_stores/';
    const FRONTEND_STORES = 'stores_board/frontend_stores/';
    const ADMIN_STORES = 'stores_board/admin_stores/';
    const ADMIN_STORES_PAGIANTION = 'stores_board/admin_stores_pagination/stores_pagination_per_page';

    /**
     * get config value
     * @return mixed
     */
    public function getStoresEnabled()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_STORES . 'enabled',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getMediaPath()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_STORES . 'media_directory_path',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getAzimuthOrientation()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_STORES . 'azimuth_orientation',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getFrontMainTitle()
    {
        return $this->scopeConfig->getValue(
            self::FRONTEND_STORES . 'main_title',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getFrontSubTitle()
    {
        return $this->scopeConfig->getValue(
            self::FRONTEND_STORES . 'sub_title',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getFrontMapHeight()
    {
        return $this->scopeConfig->getValue(
            self::FRONTEND_STORES . 'map_height',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getFrontMapZoom()
    {
        return $this->scopeConfig->getValue(
            self::FRONTEND_STORES . 'map_zoom',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendDefaultLocation()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'default_location',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendMapHeight()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'map_height',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendInfoWindowWeight()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'info_window_weight',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendInfoWindowHeight()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'info_window_height',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendIconContent()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'icon_content',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get config value
     * @return mixed
     */
    public function getBackendMapZoom()
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES . 'map_zoom',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get default country
     * @return mixed
     */
    public function getCurrentCountry()
    {
        return $this->scopeConfig->getValue(
            'general/country/default',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * get Api key from config
     * @return mixed
     */
    public function getApiKey()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_STORES . 'map_api_key',
            ScopeInterface::SCOPE_STORE
        );
    }
    /**
     * get Api key from config
     * @return mixed
     */
    public function getGeoCodeApiKey()
    {
        return $this->scopeConfig->getValue(
            self::GENERAL_STORES . 'map_api_key_geocode',
            ScopeInterface::SCOPE_STORE
        );
    }

    /**
     * This function will return stores per page
     * @param null $scopeCode
     * @return int
     */
    public function getPaginationLimit($scopeCode = null)
    {
        return $this->scopeConfig->getValue(
            self::ADMIN_STORES_PAGIANTION,
            ScopeInterface::SCOPE_STORE,
            $scopeCode
        );
    }
}
