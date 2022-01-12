<?php

namespace CJ\LineShopping\Helper;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Stdlib\CookieManagerInterface;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;

class Data
{
    const LINE_SHOPPING_ECID_COOKIE_NAME = 'line_ecid';
    const LINE_SHOPPING_INFORMATION_COOKIE_NAME = 'line-information';
    const LINE_INFO = [
        'utm_campaign',
        'utm_source',
        'utm_medium'
    ];
    const TIME_FORMAT_YMDHIS = 'Y-m-d H:i:s';
    const IS_LINE_SHOPPING = 'is_line_shopping';
    const IS_SENT_FEE_POST_BACK = 'is_sent_fee_post_back';
    const IS_SENT_ORDER_POST_BACK = 'is_sent_order_post_back';

    /**
     * @var DirectoryList
     */
    protected DirectoryList $dir;

    /**
     * @var File
     */
    protected File $file;

    /**
     * @var FileDriver
     */
    protected FileDriver $fileDriver;

    /**
     * @var CookieManagerInterface
     */
    protected CookieManagerInterface $cookieManager;

    /**
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

    /**
     * @param TimezoneInterface $timezone
     * @param CookieManagerInterface $cookieManager
     * @param DirectoryList $dir
     * @param File $file
     * @param FileDriver $fileDriver
     */
    public function __construct(
        TimezoneInterface $timezone,
        CookieManagerInterface $cookieManager,
        DirectoryList $dir,
        File $file,
        FileDriver $fileDriver
    ) {
        $this->timezone = $timezone;
        $this->cookieManager = $cookieManager;
        $this->dir = $dir;
        $this->file = $file;
        $this->fileDriver = $fileDriver;
    }

    /**
     * @param $result
     * @param $order
     * @param $type
     * @return void
     */
    public function updateOrderHistory($result, $order, $type)
    {
        if ($result) {
            $message = ($type == 'order') ?
                'Line Shopping: order post back successfully.' :
                'Line Shopping: fee post back successfully.';
            $order->addStatusToHistory(
                $order->getStatus(),
                __($message)
            );
            $order->save();
        }
    }

    /**
     * @param $order
     * @param $column
     * @param $value
     * @return void
     */
    public function updateOrderData($order, $column, $value)
    {
        $order->setData($column, $value);
        $order->save();
    }

    /**
     * @return string|null
     */
    public function getLineEcidCookie()
    {
        return $this->cookieManager->getCookie(self::LINE_SHOPPING_ECID_COOKIE_NAME);
    }

    /**
     * @return string|null
     */
    public function getLineInfomationCookie()
    {
        return $this->cookieManager->getCookie(self::LINE_SHOPPING_INFORMATION_COOKIE_NAME);
    }

    /**
     * @param $date
     * @param $timezone
     * @return string
     * @throws \Exception
     */
    public function convertTimeZone($date, $timezone)
    {
        $dt = new \DateTime($date);
        $timezone = new \DateTimeZone($timezone);
        $dt->setTimezone($timezone);
        return $dt->format(self::TIME_FORMAT_YMDHIS);
    }

    /**
     * @param $order
     * @return bool
     */
    public function isValidToFeePostBack($order): bool
    {
        //order with partial refund/full refund/closed excluded
        $canceled = $order->getTotalCanceled();
        $remain = $order->getGrandTotal() - $order->getTotalRefunded();
        if ($remain <= 0 || $canceled) {
            return false;
        }
        return true;
    }
}
