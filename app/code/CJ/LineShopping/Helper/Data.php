<?php

namespace CJ\LineShopping\Helper;

use Magento\Framework\Filesystem\DirectoryList;
use Magento\Framework\Filesystem\Io\File;
use Magento\Framework\Filesystem\Driver\File as FileDriver;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory as OrderCollectionFactory;

class Data
{
    const TIME_FORMAT_YMDHIS = 'Y-m-d H:i:s';
    const IS_LINE_SHOPPING = 'is_line_shopping';
    const IS_SENT_FEE_POST_BACK = 'is_sent_fee_post_back';
    const IS_SENT_ORDER_POST_BACK = 'is_sent_order_post_back';
    const IS_NEW_MEMBER = 'is_new_member';

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
     * @var TimezoneInterface
     */
    protected TimezoneInterface $timezone;

	/**
	 * @var OrderCollectionFactory
	 */
    protected OrderCollectionFactory $orderCollectionFactory;

    /**
     * @param TimezoneInterface $timezone
     * @param DirectoryList $dir
     * @param File $file
     * @param FileDriver $fileDriver
     * @param OrderCollectionFactory $orderCollectionFactory
     */
    public function __construct(
        TimezoneInterface      $timezone,
        DirectoryList          $dir,
        File                   $file,
        FileDriver             $fileDriver,
        OrderCollectionFactory $orderCollectionFactory
    )
    {
        $this->timezone = $timezone;
        $this->dir = $dir;
        $this->file = $file;
        $this->fileDriver = $fileDriver;
        $this->orderCollectionFactory = $orderCollectionFactory;
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
     * @param $date
     * @param $timezone
     * @return string
     * @throws \Exception
     */
    public function convertTimeZone($date, $timezone)
    {
        if (!$date instanceof \DateTime) {
            $date = new \DateTime($date);
        }
        $timezone = new \DateTimeZone($timezone);
        $date->setTimezone($timezone);
        return $date->format(self::TIME_FORMAT_YMDHIS);
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

    /**
     * @param $order
     * @return bool
     */
    public function isNewMember($order)
    {
        /** @var OrderCollectionFactory $orderColelction */
        $orderCollection = $this->orderCollectionFactory->create()
            ->addFieldToFilter(self::IS_LINE_SHOPPING, 1)
            ->addAttributeToFilter('customer_email', $order->getCustomerEmail());
        return $orderCollection->count() < 1;
    }
}
