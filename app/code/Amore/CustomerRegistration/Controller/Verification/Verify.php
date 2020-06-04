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

use Magento\Framework\App\ActionInterface;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Amore\CustomerRegistration\Model\Verification;

/**
 * To verify code of the customer
 * Class Verify
 * @package Amore\CustomerRegistration\Controller\Verification
 */
class Verify implements ActionInterface
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
     * @var Verification
     */
    private $verification;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification
        )
    {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
    }

    /**
     * To verify the code send to the customer against the mobile number
     * It wil verify the code send to the customer against the mobile number
     * @return \Magento\Framework\App\ResponseInterface|\Magento\Framework\Controller\Result\Json|\Magento\Framework\Controller\ResultInterface
     */
    public function execute()
    {
        $result['verify'] = false;
        $mobileNumber = $this->request->getParam('mobileNumber');
        $verificationCode = $this->request->getParam('code');

        try {
            $verificationResult = $this->verification->verifyCode($mobileNumber, $verificationCode);
            if($verificationResult === true)
            {
                $result['message'] = __('Code has been verified please move to the next step');
                $result['verify'] = true;
            }else if($verificationResult === false){
                $result['message'] = __('Verification code is wrong');
            }else{
                $result['message'] = $verificationResult;
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