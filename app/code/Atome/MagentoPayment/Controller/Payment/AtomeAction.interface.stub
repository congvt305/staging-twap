<?php
namespace Atome\MagentoPayment\Controller\Payment;

abstract class AtomeAction extends \Magento\Framework\App\Action\Action implements \Magento\Framework\App\CsrfAwareActionInterface
{
    use Response;

    public function validateForCsrf(\Magento\Framework\App\RequestInterface $request): ?bool
    {
        return true;
    }

    public function createCsrfValidationException(\Magento\Framework\App\RequestInterface $request): ?\Magento\Framework\App\Request\InvalidRequestException
    {
        return null;
    }
}
