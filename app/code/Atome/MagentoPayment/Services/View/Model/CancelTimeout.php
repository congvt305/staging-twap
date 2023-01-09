<?php

namespace Atome\MagentoPayment\Services\View\Model;

use Atome\MagentoPayment\Services\Config\Atome;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\ValidatorException;
use Zend_Validate_Between;

class CancelTimeout extends Value
{

    public function beforeSave()
    {
        $rule = new Zend_Validate_Between([
            'min' => ATOME::CANCEL_TIMEOUT_MINIMUM_MINUTES,
            'max' => ATOME::CANCEL_TIMEOUT_MAXIMUM_MINUTES,
            'inclusive' => true
        ]);
        $value = intval($this->getValue());

        if (!$rule->isValid($value)) {
            throw new ValidatorException(__("`{$this->getData('field_config/label')}` " . $rule->getMessages()[Zend_Validate_Between::NOT_BETWEEN]));
        }

        $this->setValue($value);

        parent::beforeSave();
    }


}
