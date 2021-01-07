<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 23/12/2020
 * Time: 11:20 AM
 */
namespace Amore\CustomerRegistration\Controller\Verification;

use Magento\Framework\App\Action\Action;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\Result\Json;
use Magento\Framework\Controller\Result\JsonFactory;
use Magento\Framework\App\Action\Context;
use Magento\Framework\Controller\ResultInterface;
use Amore\CustomerRegistration\Model\POSSystem;

/**
 * To verify ba code of the customer
 *
 * Class VerifyBACode
 */
class VerifyBACode extends Action
{
    /**
     * @var JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var POSSystem
     */
    private $posSystem;

    /**
     * @param Context $context
     * @param POSSystem $posSystem
     * @param JsonFactory $resultJsonFactory
     */
    public function __construct(
        Context $context,
        POSSystem $posSystem,
        JsonFactory $resultJsonFactory
    ) {
        $this->posSystem = $posSystem;
        $this->resultJsonFactory = $resultJsonFactory;
        parent::__construct($context);
    }

    /**
     * To verify the ba code send of customer from POS
     *
     * @return ResponseInterface|Json|ResultInterface
     */
    public function execute()
    {
        $result = [
            'verify' => false,
            'message' => __('Unable to fetch information at this time')
        ];
        $baCode = $this->getRequest()->getParam('baCode');
        $websiteId = $this->getRequest()->getParam('websiteId');
        $saleOrgCode = $this->getRequest()->getParam('saleOrgCode');
        $saleOffCode = $this->getRequest()->getParam('saleOffCode');

        if (isset($baCode) && $baCode) {
            try {
                $verificationResult = $this->posSystem->callBACodeInfoApi(
                    $baCode,
                    $websiteId,
                    $saleOrgCode,
                    $saleOffCode
                );
                if (isset($verificationResult['verify'])) {
                    $result['verify'] = $verificationResult['verify'] ? true : false;
                    if (isset($verificationResult['message']) && $verificationResult['message']) {
                        $result['message'] = $verificationResult['message'];
                    }
                }
            } catch (\Exception $e) {
                $result['message'] = $e->getMessage();
            }
        }

        $jsonResult = $this->resultJsonFactory->create();
        $jsonResult->setData($result);
        return $jsonResult;
    }
}
