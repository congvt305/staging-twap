<?php
declare(strict_types=1);

namespace CJ\Rewards\Controller\Ajax;

use Amasty\Rewards\Api\CheckoutRewardsManagementInterface;
use CJ\Rewards\Model\Data;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;
use Psr\Log\LoggerInterface;

class RewardPost implements HttpPostActionInterface
{
    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CheckoutRewardsManagementInterface
     */
    private $rewardsManagement;

    /**
     * @var \Magento\Checkout\Model\Session
     */
    private $_checkoutSession;

    /**
     * @var \Magento\Framework\Controller\Result\JsonFactory
     */
    private $resultJsonFactory;

    /**
     * @var \Magento\Framework\App\RequestInterface
     */
    private $request;

    /**
     * @var Data
     */
    private $rewardsData;

    /**
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param LoggerInterface $logger
     * @param CheckoutRewardsManagementInterface $rewardsManagement
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Data $rewardsData
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        LoggerInterface $logger,
        CheckoutRewardsManagementInterface $rewardsManagement,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $request,
        Data $rewardsData
    ) {

        $this->logger = $logger;
        $this->rewardsManagement = $rewardsManagement;
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->rewardsData = $rewardsData;
    }

    /**
     * @return Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $applyCode = $this->request ->getParam('remove') == 1 ? 0 : 1;
        $cartQuote = $this->_checkoutSession->getQuote();
        $usedPoints = (float)$this->request->getParam('amreward_amount', 0); //parse it to float
        $result = ['success' => true];
        $jsonResult = $this->resultJsonFactory->create();
        if (!$this->request->isAjax()) {
            $jsonResult->setData(
                [
                    'success' => false,
                    'message' => __('Sorry, something went wrong. Please try again later.')
                ]
            );
            return $jsonResult;
        }
        if ($applyCode) {
            if ($message = $this->rewardsData->isExcludeDay()) {
                $result['message'] = __('You can not use reward point at %1', $message);
                $result['success'] = false;
            }
            if (!$this->rewardsData->canUseRewardPoint($cartQuote)) {
                $result['message'] = __('You can\'t use point right now');
                $result['success'] = false;
            }
            $isUsePointOrMoney = $this->rewardsData->isUsePointOrMoney();
            if ($isUsePointOrMoney == \CJ\Rewards\Model\Config::USE_MONEY_TO_GET_DISCOUNT) {
                if (!is_int($usedPoints)) {
                    $result['message'] = __('The amount must be greater than 0 and must be integer');
                    $result['success'] = false;
                }
                $usedPoints = $usedPoints * (int)$this->rewardsData->getPointsRate();
            }

            try {
                if ($result['success']) {
                    $this->rewardsManagement->set($cartQuote->getId(), $usedPoints);
                }
            } catch (\Magento\Framework\Exception\LocalizedException $e) {
                $result['message'] = $e->getMessage();
                $result['success'] = false;
            } catch (\Exception $e) {
                $result['message'] = __('We cannot Reward.');
                $result['success'] = false;
                $this->logger->critical($e);
            }
        }

        /**
         *Json Result
         *
         * @var Json $jsonResult
         */
        $jsonResult->setData($result);
        return $jsonResult;
    }
}
