<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 14/10/20
 * Time: 06:48 PM
 */
namespace Eguana\NewsBoard\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class Dynamic Category create in configuration
 */
class Category extends AbstractFieldArray
{
    /**
     * Prepare existing row data object
     */
    protected function _prepareToRender()
    {
        $this->addColumn(
            'attribute_name',
            [
                'label' => __('Name'),
                'class' => 'required-entry',
            ]
        );
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
