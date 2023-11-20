<?php

namespace Sapt\AjaxWishlist\ViewModel;

use Magento\Framework\App\Action\Context;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Sapt\AjaxWishlist\Helper\Data;

class AjaxWishlistStatus implements ArgumentInterface
{
    /**
     * @var HelperData
     */
    protected $helperData;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * AjaxWishlistStatus constructor.
     *
     * @param Data $helperData
     * @param Context $context
     */
    public function __construct(Data $helperData, Context $context)
    {
        $this->helperData = $helperData;
        $this->request = $context->getRequest();
    }

    /**
     * Check module status
     *
     * @return bool
     */
    public function moduleStatus()
    {
        return $this->helperData->isModuleEnabled();
    }

    /**
     * To check is wishlist page
     *
     * @return bool
     */
    public function isWishlistPage()
    {

        return $this->request->getFullActionName() == "wishlist_index_index";
    }
}
