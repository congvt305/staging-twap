<?php
    namespace Hoolah\Hoolah\Model\Config;
    
    use Magento\Framework\App\Config\Value;
    
    /**
     * Serialized backend model
     *
     * @api
     * @since 100.0.2
     */
    class TrimmedValue extends Value
    {
        public function beforeSave()
        {
            $this->setValue(trim($this->getValue()));
            
            return parent::beforeSave();
        }
    }
