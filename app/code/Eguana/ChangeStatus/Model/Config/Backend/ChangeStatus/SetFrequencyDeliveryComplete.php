<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2019 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: raheel
 * Date: 19/4/21
 * Time: 3:55 PM
 */
namespace Eguana\ChangeStatus\Model\Config\Backend\ChangeStatus;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Config\Value;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\Data\Collection\AbstractDb;
use Magento\Framework\Model\Context;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Registry;
use Psr\Log\LoggerInterface;

/**
 * Class SetFrequencyDeliveryComplete
 *
 * Config model class to set frequency value
 */
class SetFrequencyDeliveryComplete extends Value
{
    /**#@+
     * Constants for cron
     */
    const TIME_VALUE_PATH       = 'groups/change_order_status_cron/fields/time/value';
    const CRON_STRING_PATH      = 'crontab/default/jobs/eguana_delivery_complete_status_cron/schedule/cron_expr';
    const FREQUENCY_VALUE_PATH  = 'groups/change_order_status_cron/fields/frequency/value';
    /**#@-*/

    /**
     * @var ValueFactory
     */
    private $configValueFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @param Context $context
     * @param Registry $registry
     * @param ScopeConfigInterface $config
     * @param TypeListInterface $cacheTypeList
     * @param ValueFactory $configValueFactory
     * @param LoggerInterface $logger
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
        LoggerInterface $logger,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        $this->logger = $logger;
        $this->configValueFactory = $configValueFactory;
        parent::__construct(
            $context,
            $registry,
            $config,
            $cacheTypeList,
            $resource,
            $resourceCollection,
            $data
        );
    }

    /**
     * After save method to set frequency
     *
     * @return SetFrequencyDeliveryComplete
     */
    public function afterSave()
    {
        $time = $this->getData(self::TIME_VALUE_PATH);
        $frequency = $this->getData(self::FREQUENCY_VALUE_PATH);

        $cronExprArray = [
            (int)$time[1],
            (int)$time[0],
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
}
