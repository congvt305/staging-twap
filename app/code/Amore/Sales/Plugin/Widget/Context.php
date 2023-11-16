<?php

namespace Amore\Sales\Plugin\Widget;

use Magento\Backend\Block\Widget\Button\ButtonList;
use Magento\Backend\Block\Widget\Button\Toolbar as ToolbarContext;
use Magento\Framework\Registry;
use Magento\Framework\View\Element\AbstractBlock;
use Magento\Sales\Block\Adminhtml\Order\View;

class Context
{
    /**
     * @var Registry
     */
    protected $coreRegistry;

    /**
     * @param Registry $coreRegistry
     */
    public function __construct(
        Registry $coreRegistry
    ) {
        $this->coreRegistry = $coreRegistry;
    }

    /**
     * @param ToolbarContext $toolbar
     * @param AbstractBlock $context
     * @param ButtonList $buttonList
     * @return array
     */
    public function beforePushButtons(
        ToolbarContext $toolbar,
        AbstractBlock  $context,
        ButtonList     $buttonList
    ) {
        if (!$context instanceof View) {
            return [$context, $buttonList];
        }
        //ITO0306-54: [LNG/SWS/AP HK] Hide the Hold function from the order detail page
        $buttonList->remove('order_hold');

        return [$context, $buttonList];
    }

}
