<?php
declare(strict_types=1);
namespace CJ\Security\Plugin;

class AdminSessionInfo
{
    
    public function aroundIsSessionExpired(
        \Magento\Security\Model\AdminSessionInfo $subject,
         callable $proceed
        ){
            if (is_null($subject->getUpdatedAt())) return true;
            return $proceed();
            
    }
    
}

