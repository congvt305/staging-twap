<?php
/**
 * Created by PhpStorm
 * User: abbas
 * Date: 20. 5. 25
 * Time: 오전 11:33
 *
 * PHP version 7.3.18
 *
 * @category PHP_FILE
 * @package  Eguana
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Amore\CustomerRegistration\Model\Verification;
use Magento\Framework\Controller\ResultInterface;

/**
 * To verify code of the customer
 * Class Verify
 *
 * @category PHP_FILE
 * @package  Amore\CustomerRegistration\Controller\Verification
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */
class Verify implements ActionInterface
{
    /**
     * Json Factory
     *
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * Request Interface
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
     * Verify constructor.
     *
     * @param Context      $context           Context
     * @param JsonFactory  $resultJsonFactory Result Json Factory
     * @param Verification $verification      Verification
     */
    public function __construct(
        Context $context,
        JsonFactory $resultJsonFactory,
        Verification $verification
    ) {
        $this->request = $context->getRequest();
        $this->resultJsonFactory = $resultJsonFactory;
        $this->verification = $verification;
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

        try {
            $verificationResult = $this->verification
                ->verifyCode($mobileNumber, $verificationCode);

            if ($verificationResult === true) {
                $result['message'] = __(
                    'Code has been verified please move to the next step'
                );
                $result['verify'] = true;
            } else if ($verificationResult === false) {
                $result['message'] = __('Verification code is wrong');
            } else {
                $result['message'] = $verificationResult;
            }

        }catch (\Exception $e){
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