<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: Shahroz
 * Date: 11/15/19
 * Time: 1:09 PM
 */
namespace Eguana\CustomerBulletin\Model\Config;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;

/**
 * This class is use for the configuration setting
 *
 * Class CronConfig
 */
class CronConfig extends Value
{
    /**
     * Cron string path
     */
    public const CLOSE_CRON_STRING_PATH = 'crontab/default/jobs/eguana_ticket_close_cron/schedule/cron_expr';

    /**
     * Cron Fields
     */
    public const TIME_FIELD_CLOSE = 'close_time';
    public const FREQUENCY_FIELD_CLOSE = 'close_frequency';

    /**
     * @var ValueFactory
     */
    protected $configValueFactory;

    /**
     * @var string
     */
    protected $runModelPath = '';

    /**
     * CronConfig constructor.
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param AbstractResource|null $resource
     * @param AbstractDb|null $resourceCollection
     * @param array $data
     */
    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->configValueFactory = $configValueFactory;
        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * Converts the Cron fields in admin to Cron expression and saves in cron value
     *
     * @return Value
     */
    public function afterSave()
    {
        $timeField = self::TIME_FIELD_CLOSE;
        $frequencyField = self::FREQUENCY_FIELD_CLOSE;
        $cronStringPath = self::CLOSE_CRON_STRING_PATH;
        $time = $this->getData('groups/configurable_cron/fields/' . $timeField . '/value');
        $frequency = $this->getData('groups/configurable_cron/fields/' . $frequencyField . '/value');

        $cronExprArray = [
            (int)$time[1], //Minute
            (int)$time[0], //Hour
            $frequency == Frequency::CRON_MONTHLY ? '1' : '*', //Day of the Month
            '*', //Month of the Year
            $frequency == Frequency::CRON_WEEKLY ? '1' : '*', //Day of the Week
        ];

        $cronExprString = join(' ', $cronExprArray);

        $this->configValueFactory->create()->load(
            $cronStringPath,
            'path'
        )->setValue(
            $cronExprString
        )->setPath(
            $cronStringPath
        )->save();

        return parent::afterSave();
    }
}
