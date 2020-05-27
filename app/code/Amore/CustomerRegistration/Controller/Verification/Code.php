<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오전 11:33
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Amore\CustomerRegistration\Model\Verification;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;

/**
 * To send the verification code to the customer
 * Class Code
 * @package Amore\CustomerRegistration\Controller\Verification
 */
class Code implements ActionInterface
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var \Amore\CustomerRegistration\Model\Verification
     */
    private $verification;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification)
    {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
    }

    /**
     * To send the code
     * To seend the verification code to the customer
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result['send'] = false;
        $mobileNumber = $this->request->getParam('mobileNumber');

        try {
            $sendVerificationCodeResult = $this->verification->sendVerificationCode($mobileNumber);

            if($sendVerificationCodeResult === true)
            {
                $result['send'] = $sendVerificationCodeResult;
            }else{
                $result['message'] = $sendVerificationCodeResult;
            }
        }catch (\Exception $e){
            $result['message'] = $e->getMessage();
        }
        /** @var  \Magento\Framework\Controller\Result\Json $jsonResult */
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($result);
        return $jsonResult;
    }
}