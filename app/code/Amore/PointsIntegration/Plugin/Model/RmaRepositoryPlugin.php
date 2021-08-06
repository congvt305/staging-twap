<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-28
 * Time: 오후 5:02
 */

namespace Amore\PointsIntegration\Plugin\Model;

use Amore\PointsIntegration\Exception\PosPointsException;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Store\Model\StoreManagerInterface;

class RmaRepositoryPlugin
{
    /**
     * @var \Amore\PointsIntegration\Model\Source\Config
     */
    private $config;
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;
    /**
     * @var \Amore\PointsIntegration\Logger\Logger
     */
    private $logger;
    /**
     * @var \Magento\Framework\Event\ManagerInterface
     */
    private $eventManager;
    /**
     * @var Json
     */
    private $json;
    /**
     * @var \Amore\PointsIntegration\Model\PosReturnData
     */
    private $posReturnData;
    /**
     * @var \Amore\PointsIntegration\Model\Connection\Request
     */
    private $request;

    /**
     * RmaPlugin constructor.
     * @param \Amore\PointsIntegration\Model\Source\Config $config
     * @param StoreManagerInterface $storeManager
     * @param \Amore\PointsIntegration\Logger\Logger $logger
     * @param \Magento\Framework\Event\ManagerInterface $eventManager
     * @param Json $json
     * @param \Amore\PointsIntegration\Model\PosReturnData $posReturnData
     * @param \Amore\PointsIntegration\Model\Connection\Request $request
     */
    public function __construct(
        \Amore\PointsIntegration\Model\Source\Config $config,
        StoreManagerInterface $storeManager,
        \Amore\PointsIntegration\Logger\Logger $logger,
        \Magento\Framework\Event\ManagerInterface $eventManager,
        Json $json,
        \Amore\PointsIntegration\Model\PosReturnData $posReturnData,
        \Amore\PointsIntegration\Model\Connection\Request $request
    )
    {
        $this->config = $config;
        $this->storeManager = $storeManager;
        $this->logger = $logger;
        $this->eventManager = $eventManager;
        $this->json = $json;
        $this->posReturnData = $posReturnData;
        $this->request = $request;
    }

    public function afterSave(\Magento\Rma\Model\RmaRepository $subject, $rma)
    {
        $websiteId = $rma->getOrder()->getStore()->getWebsiteId();
        $moduleEnableCheck = $this->config->getActive($websiteId);
        $rmaSendingEnableCheck = $this->config->getPosRmaActive($websiteId);
        $completedStatus = 'processed_closed';
        $posRmaCompletedSent = $rma->getData('pos_rma_completed_sent');

        if ($moduleEnableCheck && $rmaSendingEnableCheck) {
            if (!$posRmaCompletedSent && $rma->getStatus() == $completedStatus) {
                $rma->setData('pos_rma_completed_send', true);
            }
        }

        return $rma;
    }
}
