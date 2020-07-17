<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/12/20
 * Time: 8:16 AM
 */
declare(strict_types=1);

namespace Eguana\CustomerRefund\Model;

use Psr\Log\LoggerInterface;

class BankInfo extends \Magento\Framework\Model\AbstractModel implements \Eguana\CustomerRefund\Api\Data\BankInfoDataInterface
{
    /**
     * @var Cryptographer
     */
    private $cryptographer;
    /**
     * @var LoggerInterface
     */
    private $logger;

    public function __construct(
        \Eguana\CustomerRefund\Model\Cryptographer $cryptographer,
        \Psr\Log\LoggerInterface $logger,
        \Magento\Framework\Model\Context $context,
        \Magento\Framework\Registry $registry,
        \Magento\Framework\Model\ResourceModel\AbstractResource $resource = null,
        \Magento\Framework\Data\Collection\AbstractDb $resourceCollection = null,
        array $data = []
    ) {
        parent::__construct($context, $registry, $resource, $resourceCollection, $data);
        $this->cryptographer = $cryptographer;
        $this->logger = $logger;
    }

    /**
     * Prefix of model events names
     *
     * @var string
     */
    protected $_eventPrefix = 'eguana_customerrefund_bankinfo';

    protected function _construct()
    {
        parent::_construct();
        $this->_init(\Eguana\CustomerRefund\Model\ResourceModel\BankInfo::class);
    }

    /**
     * @return int|null
     */
    public function getBankInfoId(): ?int
    {
        return $this->getData(self::BANKINFO_ID);
    }

    /**
     * @param int|null $bankInfoId
     * @return void
     */
    public function setBankInfoId(?int $bankInfoId): void
    {
        $this->setData(self::BANKINFO_ID, $bankInfoId);
    }

    /**
     * @return string|null
     */
    public function getBankName(): ?string
    {
        return $this->getData(self::BANK_NAME);
    }

    /**
     * @param string|null $bankName
     * @return void
     */
    public function setBankName(?string $bankName): void
    {
        $this->setData(self::BANK_NAME, $bankName);
    }

    /**
     * @return string|null
     */
    public function getAccountOwnerName(): ?string
    {
        return $this->getData(self::ACCOUNT_OWNER_NAME);
    }

    /**
     * @param string|null $accountOwnerName
     * @return void
     */
    public function setAccountOwnerName(?string $accountOwnerName): void
    {
        $this->setData(self::ACCOUNT_OWNER_NAME, $accountOwnerName);
    }

    /**
     * @return string|null
     */
    public function getBankAccountNumber(): ?string
    {
        return $this->getData(self::BANK_ACCOUNT_NUMBER);
    }

    /**
     * @param string|null $bankAccountNumber
     * @return void
     */
    public function setBankAccountNumber(?string $bankAccountNumber): void
    {
        $this->setData(self::BANK_ACCOUNT_NUMBER, $bankAccountNumber);
    }

    /**
     * @return int|null
     */
    public function getOrderId(): ?int
    {
        return $this->getData(self::ORDER_ID);
    }

    /**
     * @param int|null $orderId
     */
    public function setOrderId(?int $orderId): void
    {
        $this->setData(self::ORDER_ID, $orderId);
    }

    public function beforeSave()
    {
       parent::beforeSave();
       if ($this->isObjectNew() && $this->getBankAccountNumber()) {
           try {
               $encryptData = $this->cryptographer->encode($this->getBankAccountNumber());
               if (isset($encryptData['encrypted'], $encryptData['base64iv'])) {
                   $this->setBankAccountNumber($encryptData['encrypted']);
                   $this->setData('base64iv', $encryptData['base64iv']);
               }
           } catch (\Exception $e) {
               $this->logger->critical($e);
           }
       }
       return $this;
    }

}
