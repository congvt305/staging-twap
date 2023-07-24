<?php
declare(strict_types=1);

namespace CJ\Rewards\Block\System\Config\Form\Field;

use Magento\Config\Block\System\Config\Form\Field\FieldArray\AbstractFieldArray;

class ListOptionRewardPointMultipleField  extends AbstractFieldArray
{
    /**
     * Prepare to render
     *
     * @return void
     */
    protected function _prepareToRender()
    {
        $this->addColumn('point', ['label' => __('Point'), 'class' => 'required-entry positive-number integer']);
        $this->addColumn('money', ['label' => __('Amount'), 'class' => 'required-entry positive-number integer']);
        $this->_addAfter = false;
        $this->_addButtonLabel = __('Add Point');

        parent::_prepareToRender();
    }
}
