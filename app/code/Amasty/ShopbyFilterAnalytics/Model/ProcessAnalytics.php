<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

use Amasty\ShopbyFilterAnalytics\Model\ResourceModel\TmpAnalytics;
use Magento\Framework\Session\SessionManagerInterface;
use Magento\Store\Model\StoreManager;
use Magento\Store\Model\StoreManagerInterface;

class ProcessAnalytics
{
    /**
     * @var TmpAnalytics
     */
    private $tmpStatistic;

    /**
     * @var StoreManager
     */
    private $storeManager;

    /**
     * @var ConfigProvider
     */
    private $configProvider;

    /**
     * @var SessionManagerInterface
     */
    private $sessionManager;

    public function __construct(
        StoreManagerInterface $storeManager,
        TmpAnalytics $tmpStatistic,
        ConfigProvider $configProvider,
        SessionManagerInterface $sessionManager
    ) {
        $this->tmpStatistic = $tmpStatistic;
        $this->storeManager = $storeManager;
        $this->configProvider = $configProvider;
        $this->sessionManager = $sessionManager;
    }

    public function execute(array $ids, ?int $entityId): void
    {
        if ($ids && $this->configProvider->isAnalyticsEnabled()) {
            $this->tmpStatistic->insertItems($this->prepareData($ids, $entityId));
        }
    }

    private function prepareData(array $ids, ?int $entityId): array
    {
        $data = [];
        $ids = $this->prepareOptionIds($ids);
        $filterSession = $this->prepareFilterSession($ids, (int) $entityId);
        $sessionId = $this->sessionManager->getSessionId();
        $storeId = $this->storeManager->getStore()->getId();

        foreach ($ids as $id) {
            $data[] = [
                TmpAnalytics::OPTION_ID => $id,
                TmpAnalytics::FILTER_SESSION => $filterSession,
                TmpAnalytics::SESSION_ID => $sessionId,
                TmpAnalytics::CATEGORY_ID => $entityId,
                TmpAnalytics::STORE_ID => $storeId
            ];
        }

        return $data;
    }

    /**
     * @param array $ids
     * @return int[]
     */
    private function prepareOptionIds(array $ids): array
    {
        $ids = array_map('intval', $ids);
        sort($ids);

        return $ids;
    }

    private function prepareFilterSession(array $ids, int $entityId): int
    {
        $ids[] = $entityId;

        return crc32(implode(', ', $ids));
    }
}
