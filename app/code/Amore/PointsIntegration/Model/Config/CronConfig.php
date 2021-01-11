<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: david
 * Date: 2021/01/06
 * Time: 5:04 PM
 */

namespace Amore\PointsIntegration\Model\Config;

class CronConfig extends \Magento\Framework\App\Config\Value
{
    const CRON_STRING_PATH = 'crontab/default/jobs/amore_points_cron/schedule/cron_expr';

    const CRON_MODEL_PATH = 'crontab/default/jobs/amore_points_cron/run/model';

    protected $_configValueFactory;

    protected $_runModelPath = '';

    public function __construct(
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\App\Config\ScopeConfigInterface $config,
        \Magento\Framework\App\Cache\TypeListInterface $cacheTypeList,
        \Magento\Framework\App\Config\ValueFactory $_configValueFactory,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->_runModelPath = $runModelPath;
        $this->_configValueFactory = $_configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * {@inheritdoc}
     *
     * @return $this
     * @throws \Exception
     */
    public function afterSave()
    {
        $time = $this->getData('groups/configurable_cron/fields/time/value');
        $frequency = $this->getData('groups/configurable_cron/fields/frequency/value');

        $cronExprArray = [
            $this->getCrontabSettings($frequency, intval($time[1])), //Minute
            $this->getCrontabSettings($frequency, intval($time[0])), //Hour
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == \Magento\Cron\Model\Config\Source\Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        try {
            $this->_configValueFactory->create()->load(
                self::CRON_STRING_PATH,
                'path'
            )->setValue(
                $cronExprString
            )->setPath(
                self::CRON_STRING_PATH
            )->save();
            $this->_configValueFactory->create()->load(
                self::CRON_MODEL_PATH,
                'path'
            )->setValue(
                $this->_runModelPath
            )->setPath(
                self::CRON_MODEL_PATH
            )->save();
        } catch (\Exception $e) {
            $this->_logger->error(__('We can\'t save the cron expression.'));
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
        if ($frequency == \Amore\PointsIntegration\Model\Config\Source\Frequency::CUSTOM_CRON) {
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
