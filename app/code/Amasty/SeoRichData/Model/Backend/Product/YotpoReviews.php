<?php

declare(strict_types=1);

namespace Amasty\SeoRichData\Model\Backend\Product;

use Magento\Framework\App\Config\Value as ConfigValue;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Module\Manager as ModuleManager;

class YotpoReviews extends ConfigValue
{
    /**
     * @return YotpoReviews
     * @throws LocalizedException
     */
    public function beforeSave(): YotpoReviews
    {
        if ($this->isValueChanged()
            && $this->getValue()
            && !$this->getModuleManager()->isEnabled('Amasty_SeoRichDataYotpo')
        ) {
            throw new LocalizedException(__(
                'Enable amasty/module-seo-rich-data-yotpo module to use product reviews from Yotpo in rich data.'
                . ' '
                . 'Please, run the following command in the SSH: composer require amasty/module-seo-rich-data-yotpo'
            ));
        }

        return parent::beforeSave();
    }

    private function getModuleManager(): ModuleManager
    {
        return $this->getData('module_manager');
    }
}
