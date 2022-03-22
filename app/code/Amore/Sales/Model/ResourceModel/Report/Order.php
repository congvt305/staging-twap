<?php
declare(strict_types=1);

namespace Amore\Sales\Model\ResourceModel\Report;

class Order extends \Magento\Sales\Model\ResourceModel\Report\Order
{
    /**
     * @var \Amore\Sales\Model\ResourceModel\Report\Order\CreatedatFactory
     */
    private $_customCreateDatFactory;

    /**
     * @var \Amore\Sales\Model\ResourceModel\Report\Order\UpdatedatFactory
     */
    private $_customUpdateDatFactory;

    public function __construct(
        \Magento\Framework\Model\ResourceModel\Db\Context $context,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Stdlib\DateTime\TimezoneInterface $localeDate,
        \Magento\Reports\Model\FlagFactory $reportsFlagFactory,
        \Magento\Framework\Stdlib\DateTime\Timezone\Validator $timezoneValidator,
        \Magento\Framework\Stdlib\DateTime\DateTime $dateTime,
        \Magento\Sales\Model\ResourceModel\Report\Order\CreatedatFactory $createDatFactory,
        \Magento\Sales\Model\ResourceModel\Report\Order\UpdatedatFactory $updateDatFactory,
        \Amore\Sales\Model\ResourceModel\Report\Order\CreatedatFactory $customCreateDatFactory,
        \Amore\Sales\Model\ResourceModel\Report\Order\UpdatedatFactory $customUpdateDatFactory,
        $connectionName = null
    ) {
        $this->_customCreateDatFactory = $customCreateDatFactory;
        $this->_customUpdateDatFactory = $customUpdateDatFactory;
        parent::__construct(
            $context,
            $logger,
            $localeDate,
            $reportsFlagFactory,
            $timezoneValidator,
            $dateTime,
            $createDatFactory,
            $updateDatFactory,
            $connectionName
        );
    }

    /**
     * Aggregate Orders data
     *
     * @param string|int|\DateTime|array|null $from
     * @param string|int|\DateTime|array|null $to
     * @return \Magento\Sales\Model\ResourceModel\Report\Order
     */
    public function aggregate($from = null, $to = null)
    {
        $this->_customCreateDatFactory->create()->aggregate($from, $to);
        $this->_customUpdateDatFactory->create()->aggregate($from, $to);
        $this->_setFlagData(\Magento\Reports\Model\Flag::REPORT_ORDER_FLAG_CODE);
        return $this;
    }
}
