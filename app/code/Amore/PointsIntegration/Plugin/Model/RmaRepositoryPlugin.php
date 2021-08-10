<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-28
 * Time: 오후 5:02
 */

namespace Amore\PointsIntegration\Plugin\Model;

use Amore\PointsIntegration\Exception\PosPointsException;
use Amore\PointsIntegration\Logger\Logger;
use Amore\PointsIntegration\Model\Source\Config;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Rma\Model\RmaRepository;
use Magento\Store\Model\StoreManagerInterface;

class RmaRepositoryPlugin
{
    /**
     * @var Config
     */
    private $config;
    /**
     * @var Logger
     */
    private $logger;

    /**
     * @param Config $config
     * @param Logger $logger
     */
    public function __construct(
        Config $config,
        Logger $logger
    )
    {
        $this->config = $config;
        $this->logger = $logger;
    }

    /**
     * @param RmaRepository $subject
     * @param $rma
     * @return mixed
     */
    public function afterSave(RmaRepository $subject, $rma)
    {
        $websiteId = $rma->getOrder()->getStore()->getWebsiteId();
        $moduleEnableCheck = $this->config->getActive($websiteId);
        $rmaSendingEnableCheck = $this->config->getPosRmaActive($websiteId);
        $completedStatus = 'processed_closed';
        $posRmaCompletedSent = $rma->getData('pos_rma_completed_sent');
        $posRmaCompletedSend = $rma->getData('pos_rma_completed_send');

        if ($moduleEnableCheck && $rmaSendingEnableCheck) {
            if (!$posRmaCompletedSent && !$posRmaCompletedSend && $rma->getStatus() == $completedStatus) {
                try {
                    $rma->setData('pos_rma_completed_send', true);
                    $subject->save($rma);
                } catch (\Exception $exception) {
                    $this->logger->error('POS ERROR: ' . $exception->getMessage());
                }
            }
        }

        return $rma;
    }
}
