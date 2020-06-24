<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: danish
 * Date: 11/21/19
 * Time: 5:33 PM
 */
namespace Eguana\StoreLocator\Block\Adminhtml\Edit\Button;

use Magento\Backend\Block\Widget\Context;
use Magento\Framework\UrlInterface;

/**
 * Class GenericButton
 * All buttons will inherit from this abstract class
 *  Magento\Customer\Block\Adminhtml\Edit
 */
class GenericButton
{
    /**
     * @var Context
     */
    protected $_context;

    /**
     * Url Builder
     *
     * @var UrlInterface
     */
    protected $_urlBuilder;

    /**
     * GenericButton constructor.
     * @param Context $_context
     */
    public function __construct(
        Context $_context
    ) {
        $this->_context = $_context;
        $this->_urlBuilder = $_context->getUrlBuilder();
    }

    /**
     * get store id from Request
     * @return mixed
     */
    public function getStoreInfoId()
    {
        return $this->_context->getRequest()->getParam('id');
    }

    /**
     * Generate url by route and parameters
     *
     * @param   string $route
     * @param   array $params
     * @return  string
     */
    public function getUrl($route = '', $params = [])
    {
        return $this->_urlBuilder->getUrl($route, $params);
    }
}
