<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-09-16
 * Time: 오후 3:15
 */

namespace Eguana\EInvoice\Model;

use Magento\Checkout\Model\ConfigProviderInterface;
use Magento\Framework\View\LayoutInterface;

class CustomConfigProvider implements ConfigProviderInterface
{
    /** @var LayoutInterface  */
    protected $_layout;
    protected $cmsBlock;

    public function __construct(LayoutInterface $layout, $blockId)
    {
        $this->_layout = $layout;
        $this->cmsBlock = $this->constructBlock($blockId);
    }

    public function getConfig()
    {
        return [
            'cms_block' => $this->cmsBlock
        ];
    }

    public function constructBlock($blockId){
        $block = $this->_layout->createBlock('Magento\Cms\Block\Block')
            ->setBlockId($blockId)->toHtml();
        return $block;
    }
}
