<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 10/11/20
 * Time: 10:34 PM
 */
namespace Eguana\Redemption\Model\Service;

use Eguana\Redemption\Api\Data\CounterInterface;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Eguana\Redemption\Api\CounterRepositoryInterface;
use Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration;
use Magento\Framework\App\Config\ScopeConfigInterface;
use Magento\Framework\Mail\Template\TransportBuilder;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Store\Model\StoreManagerInterface;
use Magento\Store\Model\ScopeInterface;
use Psr\Log\LoggerInterface;
use Magento\Framework\Exception\NoSuchEntityException;

/**
 * This class is used to send the email to customers
 *
 * Class SmsSender
 */
class EmailSender
{
    /**
     * Constant
     */
    const REDEMPTION_EMAIL_TEMPLATE_PATH = 'redemption/configuration/automatically_registration_email_templates';

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepositoryInterface;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var RedemptionConfiguration
     */
    private $redemptionConfig;

    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    /**
     * @var StateInterface
     */
    private $inlineTranslation;

    /**
     * @var ScopeConfigInterface
     */
    private $scopeConfig;

    /**
     * @var TransportBuilder
     */
    private $transportBuilder;

    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var CounterInterface
     */
    private $_counter;

    /**
     * Index constructor.
     *
     * @param StoreInfoRepositoryInterface $storeInfoRepositoryInterface
     * @param CounterRepositoryInterface|null $counterRepository
     * @param RedemptionConfiguration $redemptionConfig
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param ScopeConfigInterface $scopeConfig
     * @param TransportBuilder $transportBuilder
     * @param SmsSender $smsSender
     * @param LoggerInterface $logger
     */
    public function __construct(
        StoreInfoRepositoryInterface $storeInfoRepositoryInterface,
        CounterRepositoryInterface $counterRepository,
        RedemptionConfiguration $redemptionConfig,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        ScopeConfigInterface $scopeConfig,
        TransportBuilder $transportBuilder,
        SmsSender $smsSender,
        LoggerInterface $logger
    ) {
        $this->storeInfoRepositoryInterface = $storeInfoRepositoryInterface;
        $this->counterRepository = $counterRepository;
        $this->redemptionConfig = $redemptionConfig;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->scopeConfig = $scopeConfig;
        $this->transportBuilder = $transportBuilder;
        $this->smsSender = $smsSender;
        $this->logger = $logger;
    }

    /**
     * This method is used to send the email when counter add by customer
     *
     * @param $counterId
     * @throws \Magento\Framework\Exception\LocalizedException
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function sendEmail($counterId)
    {
        $templateOptions = ['area' => \Magento\Framework\App\Area::AREA_FRONTEND,
            'store' => $this->storeManager->getStore()->getId()];
        $storeId = $this->storeManager->getStore()->getId();
        $storeName = $this->storeManager->getStore()->getName();
        $templateVars = [
            'customer_name' => $this->getCustomerName($counterId),
            'counter_name' => $this->getStoreCounterName($counterId),
            'counter_link' => $this->getCounterLink($counterId),
            'store_name' => $storeName,
            'email_address' => $this->getEmail($storeId),
        ];
        $from = ['email' => $this->getEmail($storeId), 'name' => $this->getEmailSenderName($storeId)];
        $this->inlineTranslation->suspend();
        $to = $this->getCustomerEmail($counterId);
        $templateId = $this->scopeConfig->getValue(
            self::REDEMPTION_EMAIL_TEMPLATE_PATH,
            ScopeInterface::SCOPE_STORE,
            $storeId
        );
        $transport = $this->transportBuilder
            ->setTemplateIdentifier($templateId)
            ->setTemplateOptions($templateOptions)
            ->setTemplateVars($templateVars)
            ->setFrom($from)
            ->addTo($to)
            ->getTransport();
        try {
            $transport->sendMessage();
            $this->inlineTranslation->resume();
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
    }

    /**
     * Retrieve Counter by id
     * @param $counterId
     * @return CounterInterface|bool
     */
    public function getCounterById($counterId)
    {
        if ($this->_counter === null) {
            try {
                $this->_counter = $this->counterRepository->getById($counterId);
                return $this->_counter;
            } catch (NoSuchEntityException $e) {
                $this->logger->info($e->getMessage());
                return false;
            }
        }
    }

    /**
     * get customer name from its id
     * @param $counterId
     * @return string|null
     */
    public function getCustomerName($counterId)
    {
        $counter = $this->getCounterById($counterId);
        if (!$counter) {
            return null;
        }
        return $counter->getCustomerName();
    }

    /**
     * get redemption identifier from its id
     *
     * @param $counterId
     * @return string
     */
    public function getCounterLink($counterId)
    {
        $counter = $this->getCounterById($counterId);
        if (!$counter) {
            return null;
        }
        $token = $counter->getToken();
        try {
            $resultUrl = $this->storeManager->getStore()
                ->getUrl('redemption/details/register', ['counter_id'=>$counterId, 'token' => $token]);
        } catch (\Exception $e) {
            $this->logger->info($e->getMessage());
        }
        return $resultUrl;
    }

    /**
     * get Email of Sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    public function getEmail($storeId)
    {
        return $this->redemptionConfig->getEmail(
            'trans_email/ident_' . $this->getEmailSender($storeId) . '/email',
            $storeId
        );
    }

    /**
     * get id of Sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    public function getEmailSender($storeId)
    {
        return $this->redemptionConfig->getGeneralConfig('sender_email_identity', $storeId);
    }

    /**
     * get Name of Sender from configuration
     *
     * @param $storeId
     * @return mixed
     */
    public function getEmailSenderName($storeId)
    {
        return $this->redemptionConfig->getEmail(
            'trans_email/ident_' . $this->getEmailSender($storeId) . '/name',
            $storeId
        );
    }

    /**
     * get customer Email from its id
     *
     * @param $counterId
     * @return string
     */
    public function getCustomerEmail($counterId)
    {
        $counter = $this->getCounterById($counterId);
        if (!$counter) {
            return null;
        }
        return $counter->getEmail();
    }

    /**
     * get RegistrationEmail Enable value from configuration
     *
     * @param $storeId
     * @return mixed
     */
    public function getRegistrationEmailEnableValue($storeId)
    {
        return $this->redemptionConfig->getConfigValue('send_email_to_customer', $storeId);
    }

    /**
     * This method is used to get the store counter name
     *
     * @param $counterId
     * @return mixed
     */
    public function getStoreCounterName($counterId)
    {
        $counter = $this->getCounterById($counterId);
        if (!$counter) {
            return null;
        }
        $counterStoreId = $counter->getCounterId();
        $storeCounterName = $this->storeInfoRepositoryInterface->getById($counterStoreId)->getTitle();
        return $storeCounterName;
    }
}
