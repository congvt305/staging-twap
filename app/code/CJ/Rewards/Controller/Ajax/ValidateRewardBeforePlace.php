<?php
declare(strict_types=1);

namespace CJ\Rewards\Controller\Ajax;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use CJ\Rewards\Model\Data;
use Magento\Framework\App\Action\HttpPostActionInterface;
use Magento\Framework\Controller\Result\Json;

class ValidateRewardBeforePlace  implements HttpPostActionInterface
{
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
     * @param \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory
     * @param \Magento\Framework\App\RequestInterface $request
     * @param Data $rewardsData
     */
    public function __construct(
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Framework\Controller\Result\JsonFactory $resultJsonFactory,
        \Magento\Framework\App\RequestInterface $request,
        Data $rewardsData
    ) {
        $this->_checkoutSession = $checkoutSession;
        $this->resultJsonFactory = $resultJsonFactory;
        $this->request = $request;
        $this->rewardsData = $rewardsData;
    }

    /**
     * Check if can use reward point or not before place order
     *
     * @return Json
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     * @SuppressWarnings(PHPMD.NPathComplexity)
     */
    public function execute()
    {
        $cartQuote = $this->_checkoutSession->getQuote();
        $pointSpent = (float)$cartQuote->getData(EntityInterface::POINTS_SPENT);
        $result = ['success' => true];
        $jsonResult = $this->resultJsonFactory->create();
        if (!$this->request->isAjax()) {
            $jsonResult->setData(
                [
                    'success' => false,
                ]
            );
            return $jsonResult;
        }
        if ($pointSpent && ($this->rewardsData->isExcludeDay() || !$this->rewardsData->canUseRewardPoint($cartQuote))) {
            $result['success'] = false;
        }

        $jsonResult->setData($result);
        return $jsonResult;
    }
}
