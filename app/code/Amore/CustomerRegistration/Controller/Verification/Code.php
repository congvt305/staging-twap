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
 */

namespace Amore\CustomerRegistration\Controller\Verification;

use Amore\CustomerRegistration\Model\Verification;
use Magento\Framework\App\ActionInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;

/**
 * To send the verification code to the customer
 * Class Code
 *
 * @category PHP_FILE
 * @package  Amore\CustomerRegistration\Controller\Code
 * @author   Abbas Ali Butt <bangji@eguanacommerce.com>
 * @license  https://www.eguaancommerce.com Code Licence
 * @link     https://www.eguaancommerce.com
 */
class Code implements ActionInterface
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

        try {
            $sendVerificationCodeResult = $this->verification
                ->sendVerificationCode($mobileNumber);

            if ($sendVerificationCodeResult === true) {
                $result['send'] = $sendVerificationCodeResult;
            } else {
                $result['message'] = $sendVerificationCodeResult;
            }
        }catch (\Exception $e){
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