<?php

namespace CJ\DataExport\Model\Export\CronSchedule;

/**
 * Interface CronScheduleInterface
 */
interface CronScheduleInterface
{
    const SCHEDULE_ID = 'schedule_id';
    const JOB_CODE = 'job_code';
    const STATUS = 'status';
    const MESSAGES = 'messages';
    const CREATED_AT = 'created_at';
    const SCHEDULED_AT = 'scheduled_at';
    const EXECUTED_AT = 'executed_at';
    const FINISHED_AT = 'finished_at';
    const ENTITY_TYPE = 'cj_cron_schedule';
    const FILE_NAME = 'Cronlog-auto';
}
