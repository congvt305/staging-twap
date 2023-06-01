<?php
declare(strict_types=1);
namespace CJ\Rewards\Controller\Index;

use Amasty\Rewards\Api\CheckoutRewardsManagementInterface;
use CJ\Rewards\Model\Config;
use Psr\Log\LoggerInterface;

class RewardPost extends \Amasty\Rewards\Controller\Index\RewardPost
{
    /**
     * @var CheckoutRewardsManagementInterface
     */
    private $rewardsManagement;

    /**
     * @var Config
     */
    private $cjCustomConfig;

    /**
     * @var \Amasty\Rewards\Model\Config
     */
    private $amastyConfig;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param \Magento\Framework\App\Action\Context $context
     * @param \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig
     * @param \Magento\Checkout\Model\Session $checkoutSession
     * @param \Magento\Store\Model\StoreManagerInterface $storeManager
     * @param \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator
     * @param \Magento\Checkout\Model\Cart $cart
     * @param LoggerInterface $logger
     * @param CheckoutRewardsManagementInterface $rewardsManagement
     * @param Config $cjCustomConfig
     * @param \Amasty\Rewards\Model\Config $amastyConfig
     */
    public function __construct(
        \Magento\Framework\App\Action\Context $context,
        \Magento\Framework\App\Config\ScopeConfigInterface $scopeConfig,
        \Magento\Checkout\Model\Session $checkoutSession,
        \Magento\Store\Model\StoreManagerInterface $storeManager,
        \Magento\Framework\Data\Form\FormKey\Validator $formKeyValidator,
        \Magento\Checkout\Model\Cart $cart,
        LoggerInterface $logger,
        CheckoutRewardsManagementInterface $rewardsManagement,
        Config $cjCustomConfig,
        \Amasty\Rewards\Model\Config $amastyConfig
    ) {
        parent::__construct(
            $context,
            $scopeConfig,
            $checkoutSession,
            $storeManager,
            $formKeyValidator,
            $cart,
            $logger,
            $rewardsManagement
        );
        $this->rewardsManagement = $rewardsManagement;
        $this->cjCustomConfig = $cjCustomConfig;
        $this->amastyConfig = $amastyConfig;
        $this->logger = $logger;
    }

    /**
     * Override to check point follow input is money amount
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        $applyCode = $this->getRequest()->getParam('remove') == 1 ? 0 : 1;
        $cartQuote = $this->_checkoutSession->getQuote();
        $usedPoints = $this->getRequest()->getParam('amreward_amount', 0);
        //custom code here
        $isUsePointOrMoney = $this->cjCustomConfig->isUsePointOrMoney();
        if ($isUsePointOrMoney == Config::USE_MONEY_TO_GET_DISCOUNT) {
            $usedPoints = $usedPoints * $this->amastyConfig->getPointsRate();
        }
        //end custom
        try {
            if ($applyCode) {
                $pointsData = $this->rewardsManagement->set($cartQuote->getId(), $usedPoints);
                $this->messageManager->addNoticeMessage(__($pointsData['notice']));
            } else {
                $itemsCount = $cartQuote->getItemsCount();

                if ($itemsCount) {
                    $this->rewardsManagement->collectCurrentTotals($cartQuote, 0);
                }

                $this->messageManager->addSuccessMessage(__('You Canceled Reward'));
            }
        } catch (\Magento\Framework\Exception\LocalizedException $e) {
            $this->messageManager->addErrorMessage($e->getMessage());
        } catch (\Exception $e) {
            $this->messageManager->addErrorMessage(__('We cannot Reward.'));
            $this->logger->critical($e);
        }

        return $this->_goBack();
    }
}
