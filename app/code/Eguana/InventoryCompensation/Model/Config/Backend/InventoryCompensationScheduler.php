<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2021-01-27
 * Time: 오전 10:57
 */

namespace Eguana\InventoryCompensation\Model\Config\Backend;

use Eguana\InventoryCompensation\Logger\Logger;
use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class InventoryCompensationScheduler extends Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/inventory_compensation_cron/schedule/cron_expr';

    const CRON_MODEL_PATH = '';

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';
    /**
     * @var Logger
     */
    private $logger;

    /**
     * RmaCheck constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param Logger $logger
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param string $runModelPath
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        Logger $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
        $this->logger = $logger;
    }

    public function afterSave()
    {
        $time = $this->getData('groups/inventory_compensation_cron/fields/time/value');
        $frequency = $this->getData('groups/inventory_compensation_cron/fields/frequency/value');

        $cronExprArray = [
            $this->getCrontabSettings($frequency, intval($time[1])), //Minute
            $this->getCrontabSettings($frequency, intval($time[0])), //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*',
        ];
        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
        } catch (\Exception $e) {
            $this->logger->error(__('We can\'t save the cron expression.'));
        }

        return parent::afterSave();
    }

    /**
     * Get crontab settings
     *
     * @param $frequency
     * @param $time
     *
     * @return int|string
     */
    protected function getCrontabSettings($frequency, $time)
    {
        if ($frequency == \Eguana\EInvoice\Model\Config\Source\Frequency::CUSTOM_CRON) {
            if ($time == 0) {
                $crontabSettings = '*';
            } else {
                $crontabSettings = '*/' . intval($time);
            }
        } else {
            $crontabSettings = intval($time);
        }

        return $crontabSettings;
    }
}
