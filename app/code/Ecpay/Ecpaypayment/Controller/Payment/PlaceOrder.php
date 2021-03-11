<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2021/03/05
 * Time: 3:07 PM
 */

namespace Ecpay\Ecpaypayment\Controller\Payment;

use Magento\Framework\App\Action\Action as AppAction;
use Magento\Framework\App\CsrfAwareActionInterface;
use Magento\Framework\App\Request\InvalidRequestException;
use Magento\Framework\App\RequestInterface;

class PlaceOrder extends AppAction implements CsrfAwareActionInterface
{
    public function execute()
    {
        try {
            return $this->_redirect('checkout/onepage/success');
        } catch (\Exception $e) {
            throw $e;
        }
    }

    public function createCsrfValidationException(RequestInterface $request): ?InvalidRequestException
    {
        return null;
    }

    public function validateForCsrf(RequestInterface $request): ?bool
    {
        return true;
    }
}
