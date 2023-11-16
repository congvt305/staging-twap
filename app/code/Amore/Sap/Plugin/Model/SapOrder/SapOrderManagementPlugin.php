<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 18/3/21
 * Time: 4:14 PM
 */
declare(strict_types=1);

namespace Amore\Sap\Plugin\Model\SapOrder;

use Amore\Sap\Api\SapSyncStockManagementInterface;
use Amore\Sap\Logger\Logger as SapLogger;
use Amore\Sap\Model\SapOrder\SapOrderManagement;
use Amore\Sap\Model\SapProduct\SapInventoryData;
use Amore\Sap\Model\SapProduct\SapInventoryStockFactory;
use Amore\Sap\Model\SapProduct\SapProductManagement;
use Amore\Sap\Model\Source\Config;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Serialize\Serializer\Json;
use Psr\Log\LoggerInterface;

/**
 * Before Plugin to update product stock quantity
 *
 * Class SapOrderManagementPlugin
 */
class SapOrderManagementPlugin
{
    /**
     * @var SapInventoryData
     */
    private $sapInventoryData;

    /**
     * @var SapProductManagement
     */
    private $sapProductManagement;

    /**
     * @var Config
     */
    private $sapConfig;

    /**
     * @var Json
     */
    private $json;

    /**
     * @var SapInventoryStockFactory
     */
    private $sapInventoryStockFactory;

    /**
     * @var SapSyncStockManagementInterface
     */
    private $sapSyncStockManagement;

    /**
     * @var SapLogger
     */
    private $sapLogger;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Json $json
     * @param Config $sapConfig
     * @param SapLogger $sapLogger
     * @param LoggerInterface $logger
     * @param SapInventoryData $sapInventoryData
     * @param SapProductManagement $sapProductManagement
     * @param SapInventoryStockFactory $sapInventoryStockFactory
     * @param SapSyncStockManagementInterface $sapSyncStockManagement
     */
    public function __construct(
        Json $json,
        Config $sapConfig,
        SapLogger $sapLogger,
        LoggerInterface $logger,
        SapInventoryData $sapInventoryData,
        SapProductManagement $sapProductManagement,
        SapInventoryStockFactory $sapInventoryStockFactory,
        SapSyncStockManagementInterface $sapSyncStockManagement
    ) {
        $this->json = $json;
        $this->sapLogger = $sapLogger;
        $this->sapConfig = $sapConfig;
        $this->sapInventoryData = $sapInventoryData;
        $this->sapProductManagement = $sapProductManagement;
        $this->sapSyncStockManagement = $sapSyncStockManagement;
        $this->sapInventoryStockFactory = $sapInventoryStockFactory;
    }

    /**
     * @param \Exception $exception
     * @return void
     */
    private function _logException(\Exception $exception) {
        if ($this->sapConfig->getLoggingCheck()) {
            $this->sapLogger->info('ERROR WHILE STOCK UPDATE AFTER GI CALL');
            $this->sapLogger->info($exception->getMessage());
        } else {
            $this->logger->error($exception->getMessage());
        }
    }

    /**
     * Before plugin method to sync SAP stock with magento stock
     *
     * @param SapOrderManagement $subject
     * @param $data
     */
    public function beforeOrderStatus(SapOrderManagement $subject, $data)
    {
        $incrementId = $data['odrno'];
        if ($data['odrstat'] == 4) {
            try {
                $order = $subject->getOrderFromList($incrementId);
            } catch (LocalizedException $exception) {
                $this->_logException($exception);
                return [$data];
            }
            if (strpos($incrementId, "R") === false &&
                in_array($order->getStatus(), $this->sapInventoryData->getAllowedStatuses())) {
                $orderItems = $order->getItems();
                $skus = [];
                foreach ($orderItems as $item) {
                    $skus[]['MATNR'] = $item->getSku();
                }

                try {
                    $result = 'Response is empty';
                    $stockData = [];
                    $stockInfo = $this->sapInventoryData->getStockInfo($data['mallId'], $skus);
                    if (isset($stockInfo['IT_DATA']) && $stockInfo['IT_DATA']) {
                        foreach ($stockInfo['IT_DATA'] as $value) {
                            $stock = [
                                'matnr' => $value['MATNR'],
                                'labst' => $value['LABST']
                            ];
                            $stockData[] = $stock;
                        }
                        $response = $this->sapSyncStockManagement->inventorySyncStockUpdate(
                            $data['source'],
                            $data['mallId'],
                            $stockData
                        );
                        $result = [
                            'code'      => $response->getCode(),
                            'message'   => $response->getMessage(),
                            'data'      => $response->getData(),
                        ];
                        $result = $this->json->serialize($result);
                    } elseif (isset($stockInfo['message'])) {
                        $result = $stockInfo['message'];
                    }

                    if ($this->sapConfig->getLoggingCheck()) {
                        $this->sapLogger->info('STOCK UPDATE AFTER GI CALL');
                        $this->sapLogger->info($result);
                    } else {
                        $this->logger->info($result);
                    }
                } catch (\Exception $exception) {
                    $this->_logException($exception);
                }
            }
        }
        return [$data];
    }
}
