<?php
declare(strict_types=1);

namespace CJ\Sms\Cron;

class CronCleanSmsHistory
{

    /**
     * @var \CJ\Sms\Model\ResourceModel\SmsHistoryFactory
     */
    private $smsHistoryFactory;

    /**
     * @param \CJ\Sms\Model\ResourceModel\SmsHistoryFactory $smsHistoryFactory
     */
    public function __construct(
        \CJ\Sms\Model\ResourceModel\SmsHistoryFactory $smsHistoryFactory
    ) {
        $this->smsHistoryFactory = $smsHistoryFactory;
    }

    /**
     * Execute truncate all data in sms history
     *
     * @return void
     */
    public function execute()
    {
        /** @var \CJ\Sms\Model\ResourceModel\SmsHistory $smsHistoryModel */
        $smsHistoryModel = $this->smsHistoryFactory->create();
        $smsHistoryModel->deleteAllData();
    }
}
