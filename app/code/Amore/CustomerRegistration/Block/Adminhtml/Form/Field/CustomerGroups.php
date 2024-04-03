<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 16/12/20
 * Time: 5:48 PM
 */
namespace Amore\CustomerRegistration\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;
use Magento\Framework\View\Element\BlockInterface;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\DataObject;

/**
 * Class Dynamic Category create in configuration
 */
class CustomerGroups extends AbstractFieldArray
{
    private $selectLabelRenderer;

    /**
     * @var array
     */
    protected $_columns = [];

    /**
     * @var bool
     */
    protected $_addAfter = true;

    /**
     * Get group label renderer function
     *
     * @return BlockInterface
     * @throws LocalizedException
     */
    protected function _getGroupLabelRenderer()
    {
        if (!$this->selectLabelRenderer) {
            $this->selectLabelRenderer = $this->getLayout()->createBlock(
                \Amore\CustomerRegistration\Block\Adminhtml\Form\Field\SelectGroupLabel::class,
                ''
            );
            $this->selectLabelRenderer->setClass('formfields_option_select');
        }
        return $this->selectLabelRenderer;
    }

    /**
     * Prepare existing row data object
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'label',
            [
                'label' => __('Customer Group Label'),
                'class' => 'required-entry',
            ]
        );
        $this->addColumn(
            'type',
            [
                'label' => __('Customer Group Code'),
                'class' => 'required-entry',
                'renderer' => $this->_getGroupLabelRenderer(),
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
