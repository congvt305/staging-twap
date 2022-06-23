<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Block\Adminhtml\System\Config\Form\Field;

use Magento\Framework\Data\Form\Element\AbstractElement;
use \Atome\MagentoPayment\Helper\CommonHelper;

class ModuleVersion extends \Magento\Config\Block\System\Config\Form\Field
{
    protected $commonHelper;

    public function __construct(
        \Magento\Backend\Block\Template\Context $context,
        CommonHelper $commonHelper
    ) {
        parent::__construct($context);
        $this->commonHelper = $commonHelper;
    }

    protected function _getElementHtml(AbstractElement $element)
    {
        return $this->commonHelper->getModuleVersion();
    }
}
