<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오후 1:55
 */

namespace Amore\PointsIntegration\Controller\Points;

use Amore\PointsIntegration\Model\CustomerPointsSearch;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\View\Result\PageFactory;

class Index extends AbstractController
{
    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;
    /**
     * @var Config
     */
    private $config;

    /**
     * Index constructor.
     * @param Context $context
     * @param Session $customerSession
     * @param PageFactory $resultPageFactory
     * @param CustomerPointsSearch $customerPointsSearch
     * @param Config $config
     */
    public function __construct(
        Context $context,
        Session $customerSession,
        PageFactory $resultPageFactory,
        CustomerPointsSearch $customerPointsSearch,
        Config $config
    ) {
        parent::__construct($context, $customerSession, $resultPageFactory);
        $this->customerPointsSearch = $customerPointsSearch;
        $this->config = $config;
    }

    public function execute()
    {
        $customer = $this->getCustomer();
        $active = $this->config->getActive($customer->getWebsiteId());
        if (!$this->customerSession->isLoggedIn() || !$active) {
            $resultRedirect = $this->resultRedirectFactory->create();
            $resultRedirect->setPath('customer/account/login');
            return $resultRedirect;
        } else {
            return $this->resultFactory->create(ResultFactory::TYPE_PAGE);


            // block 쪽에서 아래 코드 처리하게 변경
//            $customer = $this->getCustomer();
//            $pointSearchResult = $this->customerPointsSearch->getMemberSearchResult($customer->getId(), $customer->getWebsiteId());
//            $pointSearchResult = [
//                'cstmIntgSeq' => 'TW10111111',
//                'firstname' => 'first_test',
//                'lastname' => 'last_test',
//                'cstmGradeCD' => '10',
//                'cstmGradeNM' => 'silver',
//                'availablePoint' => 200,
//                'expirePoint' => 100,
//                'expireDate' => 20211213,
//                'statusCode' => '200',
//                'statusMessage' => 'success'
//            ];

//            if (empty($pointSearchResult)) {
//                $this->messageManager->addErrorMessage(__("Request URL is empty. Please Contact the Administrator."));
//                /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
//                $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//                $resultRedirect->setUrl($this->_redirect->getRefererUrl());
//                return $resultRedirect;
//            } else {
//                if ($pointSearchResult['statusCode'] == '200') {
//                    return $this->resultFactory->create(ResultFactory::TYPE_PAGE);
//                } else {
//                    $this->messageManager->addErrorMessage(__("Point Search Error. %1", $pointSearchResult['statusMessage']));
//                    /** @var \Magento\Framework\Controller\Result\Redirect $resultRedirect */
//                    $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
//                    $resultRedirect->setUrl($this->_redirect->getRefererUrl());
//                    return $resultRedirect;
//                }
//            }
        }
    }

    public function getCustomer()
    {
        return $this->customerSession->getCustomer();
    }
}
