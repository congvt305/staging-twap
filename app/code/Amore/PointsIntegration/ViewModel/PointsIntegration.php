<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 6/7/21
 * Time: 9:06 PM
 */
declare(strict_types=1);

namespace Amore\PointsIntegration\ViewModel;

use Amore\PointsIntegration\Model\Source\Config;
use Magento\Cms\Api\BlockRepositoryInterface;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Block\ArgumentInterface;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This ViewModel is used Points Integration
 *
 * Class PointsIntegration
 */
class PointsIntegration implements ArgumentInterface
{
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var BlockRepositoryInterface
     */
    private $blockRepository;

    /**
     * @var Config
     */
    private $config;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Config $config
     * @param LoggerInterface $logger
     * @param StoreManagerInterface $storeManager
     * @param BlockRepositoryInterface $blockRepository
     */
    public function __construct(
        Config $config,
        LoggerInterface $logger,
        StoreManagerInterface $storeManager,
        BlockRepositoryInterface $blockRepository
    ) {
        $this->config           = $config;
        $this->logger           = $logger;
        $this->storeManager     = $storeManager;
        $this->blockRepository  = $blockRepository;
    }

    /**
     * Get CMS Block Identifier
     *
     * @param $id
     * @return string
     */
    public function getCmsBlockIdentifier($id) : string
    {
        $identifier = '';
        try {
            $block = $this->blockRepository->getById($id);
            $identifier = $block->getIdentifier();
        } catch (\Exception $exception) {
            $this->logger->info('Points Integration block identifier error:' . $exception->getMessage());
        }
        return $identifier;
    }

    /**
     * Get website id
     *
     * @return int
     */
    public function getWebsiteId()
    {
        $websiteId = 0;
        try {
            $websiteId = $this->storeManager->getStore()->getWebsiteId();
        } catch (NoSuchEntityException $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $websiteId;
    }

    /**
     * Get Rewards Block Identifier
     *
     * @return string
     */
    public function getRewardsBlock()
    {
        $websiteId = $this->getWebsiteId();
        $rewardsBlockId = $this->config->getRewardsBlock($websiteId);
        $blockIdentifier = '';
        if ($rewardsBlockId) {
            $blockIdentifier = $this->getCmsBlockIdentifier($rewardsBlockId);
        }
        return $blockIdentifier;
    }

    /**
     * Get Redemption Block Identifier
     *
     * @return string
     */
    public function getRedemptionBlock()
    {
        $websiteId = $this->getWebsiteId();
        $redemptionBlockId = $this->config->getRedemptionBlock($websiteId);
        $blockIdentifier = '';
        if ($redemptionBlockId) {
            $blockIdentifier = $this->getCmsBlockIdentifier($redemptionBlockId);
        }
        return $blockIdentifier;
    }

    /**
     * Get Points Block Identifier
     *
     * @return string
     */
    public function getPointsBlock()
    {
        $websiteId = $this->getWebsiteId();
        $pointsBlockId = $this->config->getPointsBlock($websiteId);
        $blockIdentifier = '';
        if ($pointsBlockId) {
            $blockIdentifier = $this->getCmsBlockIdentifier($pointsBlockId);
        }
        return $blockIdentifier;
    }
}
