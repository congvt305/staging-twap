<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오전 11:33
 *
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Amore\CustomerRegistration\Model\Verification;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;

/**
 * To send the verification code to the customer
 * Class Code
 */
class Code extends Action
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Request
     *
     * @var RequestInterface
     */
    private $request;

    /**
     * Verfication
     *
     * @var Verification
     */
    private $verification;

    /**
     * Code constructor.
     *
     * @param Context      $context           Context
     * @param JsonFactory  $resultJsonFactory Json Factory
     * @param Verification $verification      Verfication
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification
    ) {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
        parent::__construct($context);
    }

    /**
     * To send the code
     * To send the verification code to the customer
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result['send'] = false;
        $mobileNumber = $this->request->getParam('mobileNumber');
        $customerName = $this->request->getParam('firstName').' '.$this->request->getParam('lastName');

        try {
            $sendVerificationCodeResult = $this->verification
                ->sendVerificationCode($mobileNumber, $customerName);

            if ($sendVerificationCodeResult === true) {
                $result['send'] = $sendVerificationCodeResult;
            } else {
                $result['message'] = $sendVerificationCodeResult;
            }
        } catch (\Exception $e) {
            $result['message'] = $e->getMessage();
        }
        /**
         *  Json result
         *
         * @var Json $jsonResult
         */
        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($result);
        return $jsonResult;
    }
}
