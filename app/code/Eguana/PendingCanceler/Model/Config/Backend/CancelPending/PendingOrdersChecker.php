<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-08-18
 * Time: 오후 5:09
 */

namespace Eguana\PendingCanceler\Model\Config\Backend\CancelPending;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Event\ManagerInterface;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

class PendingOrdersChecker extends Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/eguana_pending_cancel_cron/schedule/cron_expr';

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
     * @var ManagerInterface
     */
    private $eventManager;

    /**
     * RmaCheck constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param ManagerInterface $eventManager
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
        ManagerInterface $eventManager,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        $this->eventManager = $eventManager;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $time = $this->getData('groups/pending_canceler_cron/fields/time/value');
        $frequency = $this->getData('groups/pending_canceler_cron/fields/frequency/value');

        $cronExprArray = [
            $this->getCrontabSettings($frequency, $time[1]), //Minute
            $this->getCrontabSettings($frequency, $time[0]), //Hour
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
            $this->eventManager->dispatch(
                "eguana_bizconnect_operation_processed",
                [
                    'topic_name' => 'pending.order.cancel.cron',
                    'direction' => 'outgoing',
                    'to' => "SAP",
                    'serialized_data' => "",
                    'status' => 2,
                    'result_message' => $e->getMessage()
                ]
            );
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
        if ($frequency == \Eguana\PendingCanceler\Model\Config\Source\Frequency::CUSTOM_CRON) {
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
