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
use Magento\Framework\Controller\ResultFactory;

/**
 * To verify the customer with the POS system
 * Class Pos
 * @package Amore\CustomerRegistration\Controller\Verification
 */
class Pos implements ActionInterface
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
     * @var SessionManagerInterface
     */
    private $sessionManager;
    /**
     * @var Verification
     */
    private $verification;
    /**
     * @var ResultFactory
     */
    private $redirectResult;

    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification,
        ResultFactory $redirectResult)
    {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
        $this->redirectResult = $redirectResult;
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
        $firstName = $this->request->getParam('firstName');
        $lastName = $this->request->getParam('firstName');


        try {
            $verificationResult = $this->verification->customerVerification($firstName, $lastName, $mobileNumber, $verificationCode);
            if($verificationResult['code'] === 6)
            {
                $result['message'] = __('Code has been verified please move to the next step');
                $result['verify'] = true;
            }else if(in_array($verificationResult['code'],[1,2,3]))
            {
                $result['message'] = $verificationResult['message'];
                $result['verify'] = false;
            }else if(in_array($verificationResult['code'],[4,5]))
            {
                $resultRedirect = $this->resultRedirect->create(ResultFactory::TYPE_REDIRECT);
               // $resultRedirect->setUrl($this->_redirect->getRefererUrl());
                $resultRedirect->setUrl($verificationResult['url']);
                return $resultRedirect;
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