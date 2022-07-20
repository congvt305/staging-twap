<?php
/**
 * Atome Payment Module for Magento 2
 *
 * @author Atome
 * @copyright 2020 Atome
 */
namespace Atome\MagentoPayment\Model\Config\Save;

class Plugin
{
    /**
     * @param \Magento\Config\Model\Config $subject
     * @param \Closure $proceed
     * @return bool|mixed
     */
    public function aroundSave(
        \Magento\Config\Model\Config $subject,
        \Closure $proceed
    ) {
        $ret = $proceed();
        // TODO: check & update config remotely
        // ...
        return $ret;
    }
}
