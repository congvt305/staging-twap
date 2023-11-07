<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 12/6/20
 * Time: 3:59 PM
 */
namespace Eguana\VideoBoard\Helper;

use Magento\Framework\App\Helper\AbstractHelper;
use Magento\Store\Model\ScopeInterface;

/**
 * Helper class get the configuration data
 *
 * Class Data
 */
class Data extends AbstractHelper
{

    /**
     * Return the config from config path
     * @param $config_path
     * @return mixed
     */
    public function getConfig($config_path)
    {
        return $this->scopeConfig->getValue(
            $config_path,
            ScopeInterface::SCOPE_STORE
        );
    }


    /**
     * This method is used in XML layout.
     * @return string
     */
    public function getCanonicalForAllVideoBoardDetail(): string
    {
        if ($id = $this->_request->getParam('id')) {
            return $this->createLink(
                $this->scopeConfig->getValue('web/secure/base_url', ScopeInterface::SCOPE_STORE) . 'videoboard/detail/index/id/' . $id
            );
        }

        return '';
    }

    /**
     * Creeate link
     *
     * @param $url
     * @return string
     */
    protected function createLink($url): string
    {
        return '<link rel="canonical" href="' . $url . '" />';
    }
}
