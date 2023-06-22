<?php
declare(strict_types=1);

namespace CJ\Rewards\Model;

use Amasty\Rewards\Api\Data\SalesQuote\EntityInterface;
use Amasty\Rewards\Model\Config;
use CJ\Rewards\Model\Config as CJCustomConfig;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;

class CheckoutRewardsManagement extends \Amasty\Rewards\Model\CheckoutRewardsManagement
{
    /**
     * @var \CJ\Rewards\Model\Config
     */
    private $cjCustomConfig;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var \Magento\Quote\Api\CartRepositoryInterface
     */
    private $quoteRepository;

    /**
     * @var \Amasty\Rewards\Api\RewardsRepositoryInterface
     */
    private $rewardsRepository;

    /**
     * @var \Amasty\Rewards\Model\Quote\Validator\CompositeValidator
     */
    private $validator;

    /**
     * @param Config $config
     * @param \Amasty\Rewards\Model\Quote $rewardsQuote
     * @param \Magento\Quote\Api\CartRepositoryInterface $quoteRepository
     * @param \Amasty\Rewards\Api\RewardsRepositoryInterface $rewardsRepository
     * @param \Amasty\Rewards\Model\Quote\Validator\CompositeValidator $validator
     * @param \CJ\Rewards\Model\Config $cjCustomConfig
     */
    public function __construct(
        \Amasty\Rewards\Model\Config $config,
        \Amasty\Rewards\Model\Quote $rewardsQuote,
        \Magento\Quote\Api\CartRepositoryInterface $quoteRepository,
        \Amasty\Rewards\Api\RewardsRepositoryInterface $rewardsRepository,
        \Amasty\Rewards\Model\Quote\Validator\CompositeValidator $validator,
        CJCustomConfig $cjCustomConfig,
    ) {
        $this->cjCustomConfig = $cjCustomConfig;
        $this->config = $config;
        $this->quoteRepository = $quoteRepository;
        $this->rewardsRepository = $rewardsRepository;
        $this->validator = $validator;
        parent::__construct($config, $rewardsQuote, $quoteRepository, $rewardsRepository, $validator);
    }

    /**
     * Custom message when use input money to discount
     *
     * {@inheritdoc}
     */
    public function set($cartId, $usedPoints)
    {
        $isUsePointOrMoney = $this->cjCustomConfig->isUsePointOrMoney();

        if (!$usedPoints || $usedPoints < 0) {
            if ($isUsePointOrMoney == CJCustomConfig::USE_MONEY_TO_GET_DISCOUNT) {
                throw new LocalizedException(__('Amount %1 not valid.', $usedPoints * $this->config->getPointsRate()));
            } else {
                throw new LocalizedException(__('Points %1 not valid.', $usedPoints));
            }

        }

        /** @var \Magento\Quote\Model\Quote $quote */
        $quote = $this->quoteRepository->get((int)$cartId);
        $minPoints = $this->config->getMinPointsRequirement($quote->getStoreId());

        if (!$quote->getItemsCount()) {
            throw new NoSuchEntityException(__('Cart %1 doesn\'t contain products', $cartId));
        }

        $pointsLeft = $this->rewardsRepository->getCustomerRewardBalance($quote->getCustomerId());

        if ($minPoints && $pointsLeft < $minPoints) {
            if ($isUsePointOrMoney == CJCustomConfig::USE_MONEY_TO_GET_DISCOUNT) {
                throw new LocalizedException(__('You need at least %1 amount to pay for the order with reward points.', $minPoints * $this->config->getPointsRate()));
            } else {
                throw new LocalizedException(
                    __('You need at least %1 points to pay for the order with reward points.', $minPoints)
                );
            }

        }

        try {
            if ($usedPoints > $pointsLeft) {
                if ($isUsePointOrMoney == CJCustomConfig::USE_MONEY_TO_GET_DISCOUNT) {
                    throw new LocalizedException(__('Exceed limit of the deductible amount'));
                } else {
                    throw new LocalizedException(__('Too much point(s) used.'));
                }
            }

            $pointsData['notice'] = '';
            $pointsData['allowed_points'] = 0;
            $this->validator->validate($quote, $usedPoints, $pointsData);
            $usedPoints = abs($pointsData['allowed_points']);
            $itemsCount = $quote->getItemsCount();

            if ($itemsCount) {
                $this->collectCurrentTotals($quote, $usedPoints);
            }
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__($e->getMessage()));
        }

        $pointsData['allowed_points'] = $quote->getData(EntityInterface::POINTS_SPENT);
        if ($isUsePointOrMoney == CJCustomConfig::USE_MONEY_TO_GET_DISCOUNT) {
            $usedNotice = __('%1 has been discounted', $pointsData['allowed_points'] / $this->config->getPointsRate());
        } else {
            $usedNotice = __('You used %1 point(s).', $pointsData['allowed_points']);
        }

        $pointsData['notice'] = $pointsData['notice'] . ' ' . $usedNotice;

        return $pointsData;
    }

}
