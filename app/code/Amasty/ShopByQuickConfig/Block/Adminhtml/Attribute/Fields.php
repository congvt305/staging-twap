<?php

declare(strict_types=1);

namespace Amasty\ShopByQuickConfig\Block\Adminhtml\Attribute;

use Amasty\Shopby\Block\Adminhtml\Product\Attribute\Edit\Tab\Shopby;
use Magento\Eav\Api\Data\AttributeInterface;

class Fields extends Shopby
{
    protected function _prepareForm()
    {
        parent::_prepareForm();

        $form = $this->getForm();

        $form->addField(
            'frontend_input',
            'hidden',
            [
                'name' => 'frontend_input',
                'value' => $this->getAttribute()->getFrontendInput(),
            ]
        );

        return $this;
    }

    protected function getAttribute(): AttributeInterface
    {
        return $this->_coreRegistry->registry('entity_attribute');
    }
}
