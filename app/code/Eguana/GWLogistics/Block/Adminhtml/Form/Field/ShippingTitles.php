<?php
declare(strict_types=1);

namespace Eguana\GWLogistics\Block\Adminhtml\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

/**
 * Class ShippingTitles
 */
class ShippingTitles extends AbstractFieldArray
{
    const SUBTYPE_COLUMN = 'sub_type';
    const TITLE_COLUMN = 'title';

    /**
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn(self::SUBTYPE_COLUMN, ['label' => __('Sub Type Code'), 'class' => 'required-entry']);
        $this->addColumn(self::TITLE_COLUMN, ['label' => __('Title'), 'class' => 'required-entry']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add');
    }
}
