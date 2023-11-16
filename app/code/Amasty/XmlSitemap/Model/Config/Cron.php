<?php

declare(strict_types=1);

namespace Amasty\XmlSitemap\Model\Config;

use Magento\Cron\Model\Config\Source\Frequency;
use Magento\Framework\App\Config\ValueFactory;
use Magento\Framework\App\Config\Value;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Model\Context;
use Magento\Framework\Registry;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\App\Cache\TypeListInterface;
use Magento\Framework\Model\ResourceModel\AbstractResource;
use Magento\Framework\Data\Collection\AbstractDb;

class Cron extends Value
{
    private const AMASTY_CRON_STRING_PATH = 'crontab/default/jobs/amasty_xml_sitemap_generate/schedule/cron_expr';
    private const AMASTY_CRON_MODEL_PATH = 'crontab/default/jobs/amasty_xml_sitemap_generate/run/model';
    private const TIME_DATA_KEY = 'groups/cron/fields/time/value';
    private const FREQUENCY_DATA_KEY = 'groups/cron/fields/frequency/value';

    /**
     * @var ValueFactory
     */
    private $valueConfigFactory;

    /**
     * @var string
     */
    private $runModelPath;

    public function __construct(
        Context $context,
        Registry $registry,
        ScopeConfigInterface $config,
        TypeListInterface $cacheTypeList,
        ValueFactory $configValueFactory,
        AbstractResource $resource = null,
        AbstractDb $resourceCollection = null,
        $runModelPath = '',
        array $data = []
    ) {
        $this->runModelPath = $runModelPath;
        $this->valueConfigFactory = $configValueFactory;

        parent::__construct($context, $registry, $config, $cacheTypeList, $resource, $resourceCollection, $data);
    }

    /**
     * @return Value
     *
     * @throws LocalizedException
     */
    public function afterSave(): Value
    {
        $timeDataVaalue = $this->getData(self::TIME_DATA_KEY);
        if ($timeDataVaalue) {
            $frequencyData = $this->getData(self::FREQUENCY_DATA_KEY);

            $expressionArray = [
                (int)$timeDataVaalue[1],
                (int)$timeDataVaalue[0],
                $frequencyData == Frequency::CRON_MONTHLY ? '1' : '*',
                '*',
                $frequencyData == Frequency::CRON_WEEKLY ? '1' : '*',
            ];
            $expression = join(' ', $expressionArray);

            try {
                $valueConfig = $this->valueConfigFactory->create();

                $valueConfig->load(self::AMASTY_CRON_STRING_PATH, 'path');
                $valueConfig->setValue($expression);
                $valueConfig->setPath(self::AMASTY_CRON_STRING_PATH);
                $valueConfig->save();

                $valueConfig = $this->valueConfigFactory->create();

                $valueConfig->load(self::AMASTY_CRON_MODEL_PATH, 'path');
                $valueConfig->setValue($this->runModelPath);
                $valueConfig->setPath(self::AMASTY_CRON_MODEL_PATH);
                $valueConfig->save();
            } catch (\Exception $e) {
                $message = __('We can\'t save the cron expression.');
                $this->_logger->debug($message);

                throw new LocalizedException($message);
            }
        }

        return parent::afterSave();
    }
}
