<?php
/**
 * Copyright © 2016 Magento. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Hoolah\Hoolah\Block;

use Magento\Framework\Phrase;
use Magento\Payment\Block\ConfigurableInfo;

//use Hoolah\Hoolah\Gateway\Response\FraudHandler;

class Info extends ConfigurableInfo
{
    protected function _prepareSpecificInformation($transport = null)
    {
        $transport = parent::_prepareSpecificInformation($transport);
        
        if (!$this->getIsSecureMode()) // admin
        {
            $order = $this->toArray();
            if ($order) $order = $order['info'];
            if ($order) $order = $order->getOrder();
            if ($order)
            {
                if ($order->getHoolahOrderContextToken())
                    $this->setDataToTransfer(
                        $transport,
                        'Hoolah order context token',
                        $order->getHoolahOrderContextToken()
                    );
                if ($order->getHoolahOrderRef())
                    $this->setDataToTransfer(
                        $transport,
                        'Hoolah order ID',
                        $order->getHoolahOrderRef()
                    );
            }
        }
        
        return $transport;
    }
}
