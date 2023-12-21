<?php

declare(strict_types=1);

namespace Amasty\ShopbyFilterAnalytics\Model;

use Magento\Framework\Stdlib\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class DateConverter
{
    /**
     * @var DateTime
     */
    private $dateTime;

    /**
     * @var TimezoneInterface
     */
    private $localeDate;

    public function __construct(DateTime $dateTime, TimezoneInterface $localeDate)
    {
        $this->dateTime = $dateTime;
        $this->localeDate = $localeDate;
    }

    /**
     * Prepare date filter for DB.
     * Date converts without timezone shifts, only date format.
     * Return in default ISO date format.
     *
     * @param $filter
     *
     * @return array
     */
    public function prepareDateFilter($filter): array
    {
        if (isset($filter['from'])) {
            $filter['from'] = $this->dateTime->formatDate($filter['from'] . ' 00:00:00');
        }
        if (isset($filter['to'])) {
            $filter['to'] = $this->dateTime->formatDate($filter['to'] . ' 23:59:59');
        }

        return $filter;
    }

    /**
     * Return datepicker ready date.
     *
     * @param string|null $offset
     *
     * @return string
     */
    public function getOutputDate(?string $offset = null): string
    {
        $dateFormat = $this->fixDateFormat($this->localeDate->getDateFormatWithLongYear());
        $dateTime = $this->localeDate->date($offset, null, true, false);

        return $this->localeDate->formatDateTime(
            $dateTime,
            \IntlDateFormatter::SHORT,
            \IntlDateFormatter::NONE,
            null,
            null,
            $dateFormat
        );
    }

    /**
     * Prepare date format for frontend.
     * Datepicker works only with format with leading zero and full year.
     *
     * @param string $format
     *
     * @return string
     */
    public function fixDateFormat(string $format): string
    {
        return preg_replace(['/d+/', '/M+/'], ['dd', 'MM'], $format);
    }
}
