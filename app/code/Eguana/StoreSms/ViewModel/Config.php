<?php

namespace Eguana\StoreSms\ViewModel;

use Magento\Framework\View\Element\Block\ArgumentInterface;
use Eguana\StoreSms\Helper\Data;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * This class is responsible for sending registration activation state
 *
 * Class Config
 */
class Config implements ArgumentInterface
{
    /**
     * @var Data
     */
    private $data;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var LoggerInterface
     */
    private $loggerInterface;

    /**
     * Config constructor.
     * @param Data $data
     * @param StoreManagerInterface $storeManager
     * @param LoggerInterface $loggerInterface
     */
    public function __construct(
        Data $data,
        StoreManagerInterface $storeManager,
        LoggerInterface $loggerInterface
    ) {
        $this->data = $data;
        $this->storeManager = $storeManager;
        $this->loggerInterface = $loggerInterface;
    }

    /**
     * This function will return boolean value of registration activation
     *
     * @return int
     */
    public function isVerificationActive()
    {
        $storeId = '';
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (\Exception $exception) {
            $this->loggerInterface->debug($exception->getMessage());
        }

        return $this->data->getVerificationActivation($storeId);
    }

    /**
     * This function will return boolean value of store sms activation status
     *
     * @return int
     */
    public function isStoreSmsActive()
    {
        $storeId = '';
        try {
            $storeId = $this->storeManager->getStore()->getId();
        } catch (\Exception $exception) {
            $this->loggerInterface->debug($exception->getMessage());
        }

        return $this->data->getActivation($storeId);
    }

}
