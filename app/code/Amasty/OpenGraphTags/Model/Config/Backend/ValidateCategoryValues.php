<?php

declare(strict_types=1);

namespace Amasty\OpenGraphTags\Model\Config\Backend;

use Magento\Framework\App\Config\Value;

class ValidateCategoryValues extends Value
{
    private const ALLOWED_VALUES = ['meta_title', 'meta_description', 'name', 'description'];

    public function beforeSave(): Value
    {
        if ($this->isValueChanged() && !in_array($this->getValue(), self::ALLOWED_VALUES)) {
            $message = __(
                '%1 is not a valid value. Please check the list of available variables in the setting tooltip',
                $this->getValue()
            );
            $this->setValue($this->getOldValue());
            $this->getData('messageManager')->addErrorMessage($message);
        }

        return parent::beforeSave();
    }
}
