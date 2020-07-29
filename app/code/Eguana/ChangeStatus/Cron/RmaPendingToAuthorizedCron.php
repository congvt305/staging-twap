<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: brian
 * Date: 2020/07/14
 * Time: 10:18 AM
 */

namespace Eguana\ChangeStatus\Cron;

use Magento\Rma\Api\RmaRepositoryInterface;
use \Psr\Log\LoggerInterface;
use \Eguana\ChangeStatus\Model\RmaStatusChanger;

class RmaPendingToAuthorizedCron
{
    /**
     * @var LoggerInterface
     */
    private $logger;
    /**
     * @var RmaStatusChanger
     */
    private $getPendingRma;
    /**
     * @var RmaRepositoryInterface
     */
    private $rmaRepository;
    /**
     * @var \Magento\Store\Model\StoreManagerInterface
     */
    private $storeManagerInterface;
    /**
     * @var \Eguana\ChangeStatus\Model\Source\Config
     */
    private $config;

    /**
     * RmaPendingToAuthorizedCron constructor.
     * @param LoggerInterface $logger
     * @param RmaStatusChanger $rmaStatusChanger
     * @param RmaRepositoryInterface $rmaRepository
     */
    public function __construct(
        LoggerInterface $logger,
        RmaStatusChanger $rmaStatusChanger,
        RmaRepositoryInterface $rmaRepository,
        \Magento\Store\Model\StoreManagerInterface $storeManagerInterface,
        \Eguana\ChangeStatus\Model\Source\Config $config,
        RmaStatusChanger $getPendingRma
    ){
        $this->logger = $logger;
        $this->rmaStatusChanger = $rmaStatusChanger;
        $this->rmaRepository = $rmaRepository;
        $this->storeManagerInterface = $storeManagerInterface;
        $this->config = $config;
        $this->getPendingRma = $getPendingRma;
    }

    public function execute()
    {
        $stores = $this->storeManagerInterface->getStores();

        foreach ($stores as $store) {
            $isCustomRmaActive = $this->config->getCustomRmaActive($store->getId());
            if ($isCustomRmaActive) {
                $pendingRmaList = $this->getPendingRma->getPendingRma($store->getId());
                foreach ($pendingRmaList as $pendingRma) {
                    $pendingRma->setStatus('authorized');
                    $this->rmaRepository->save($pendingRma);
                }
            }
        }
    }
}
