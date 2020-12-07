<?php
/**
 * Created by Eguana.
 * User: Brian
 * Date: 2020-12-02
 * Time: 오전 9:57
 */

namespace Amore\PointsIntegration\Model\Config;

use Magento\Cron\Model\Config\Source\Frequency as CronFrequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

class Frequency extends Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/eguana_order_status_cron/schedule/cron_expr';

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
     * @var LoggerInterface
     */
    private $logger;

    /**
     * RmaCheck constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param LoggerInterface $logger
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
        LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->configValueFactory = $configValueFactory;
        $this->logger = $logger;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    public function afterSave()
    {
        $time = $this->getData('groups/change_order_status_cron/fields/time/value');
        $frequency = $this->getData('groups/change_order_status_cron/fields/frequency/value');

        $cronExprArray = [
            (int)$time[1],
            (int)$time[0],
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*',
            '*',
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*',
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
}
