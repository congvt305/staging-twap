<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오전 11:33
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Amore\CustomerRegistration\Model\Verification;
use Magento\Framework\Controller\ResultInterface;
use Amore\CustomerRegistration\Model\POSSystem;

/**
 * To verify the customer with the POS system
 * Class Pos
 */
class Pos extends Action
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Request interface
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Verification
     *
     * @var Verification
     */
    private $verification;
    /**
     * @var Amore\CustomerRegistration\Model\POSSystem
     */
    private $posSystem;

    /**
     * Pos constructor.
     *
     * @param Context      $context           Context
     * @param JsonFactory  $resultJsonFactory Result Factory
     * @param Verification $verification      Verfication
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification,
        POSSystem $posSystem
    ) {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
        $this->posSystem = $posSystem;
        parent::__construct($context);
    }

    /**
     * To verify the code send to the customer against the mobile number
     * It wil verify the code send to the customer against the mobile number
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result['verify'] = false;

        $mobileNumber = $this->request->getParam('mobileNumber');
        $verificationCode = $this->request->getParam('code');
        $firstName = $this->request->getParam('firstName');
        $lastName = $this->request->getParam('lastName');

        try {
            $verificationResult = $this->verification
                ->customerVerification(
                    $firstName,
                    $lastName,
                    $mobileNumber,
                    $verificationCode
                );

            if ($verificationResult['code'] === 6) {
                $result['pos'] = $this->posSystem->getMemberInfo($firstName, $lastName, $mobileNumber);
                if (isset($result['pos']['message']) || isset($result['pos']['url'])) {
                    $result['verify'] = false;
                    if (isset($result['pos']['code'])) {
                        $result['code'] = $result['pos']['code'];
                    }
                    if (isset($result['pos']['message'])) {
                        $result['message'] = $result['pos']['message'];
                    } else {
                        $result['url'] = $result['pos']['url'];
                    }
                } else {
                    $result['verify'] = true;
                    $this->verification->currentRegistrationStep(2);
                }
            } elseif (in_array($verificationResult['code'], [1,2,3])) {
                $result['message'] = $verificationResult['message'];
                $result['verify'] = false;
            } elseif (in_array($verificationResult['code'], [4,5])) {
                $result = $verificationResult;
                $result['verify'] = false;
            } else {
                $result['message'] = $verificationResult;
            }

        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }
        /**
         *Json Result
         *
         * @var Json $jsonResult
         */
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($result);
        return $jsonResult;
    }
}
