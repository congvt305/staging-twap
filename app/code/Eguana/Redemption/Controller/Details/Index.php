<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 22/10/20
 * Time: 4:00 PM
 */
namespace Eguana\Redemption\Controller\Details;

use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\Counter;
use Eguana\Redemption\Model\CounterFactory;
use Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration;
use Eguana\Redemption\Model\Service\SmsSender;
use Eguana\Redemption\Model\Service\EmailSender;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Response\RedirectInterface;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Data\Form\FormKey\Validator;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Message\ManagerInterface;
use Magento\Framework\Stdlib\DateTime\DateTime;
use Magento\Framework\Stdlib\DateTime\TimezoneInterface;
use Magento\Framework\Translate\Inline\StateInterface;
use Magento\Framework\View\Result\PageFactory;
use Magento\Store\Model\StoreManagerInterface;
use Psr\Log\LoggerInterface;

/**
 * Class Index
 * Controller to display details about the Redemption
 */
class Index extends Action
{
    /**
     * Constant
     */
    const DATE_FORMAT = 'Y-m-d';

    /**
     * @var PageFactory
     */
    private $resultPageFactory;

    /**
     * @var Counter
     */
    private $counter;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var ResultFactory
     */
    private $result;

    /**
     * @var ManagerInterface
     */
    private $managerInterface;

    /**
     * @var Validator
     */
    private $formKeyValidator;

    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var RedirectInterface
     */
    private $redirect;

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
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var TimezoneInterface
     */
    private $timezone;

    /**
     * Index constructor.
     *
     * @param Context $context
     * @param Counter $counter
     * @param PageFactory $resultPageFactory
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param ResultFactory $result
     * @param ManagerInterface $managerInterface
     * @param Validator $formKeyValidator
     * @param CounterFactory|null $counterFactory
     * @param DateTime $date
     * @param RedirectInterface $redirect
     * @param RedemptionConfiguration $redemptionConfig
     * @param StoreManagerInterface $storeManager
     * @param StateInterface $inlineTranslation
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param SmsSender $smsSender
     * @param EmailSender $emailSender
     * @param LoggerInterface $logger
     * @param TimezoneInterface $timezone
     */
    public function __construct(
        Context $context,
        Counter $counter,
        PageFactory $resultPageFactory,
        RedemptionRepositoryInterface $redemptionRepository,
        ResultFactory $result,
        ManagerInterface $managerInterface,
        Validator $formKeyValidator,
        CounterFactory $counterFactory,
        DateTime $date,
        RedirectInterface $redirect,
        RedemptionConfiguration $redemptionConfig,
        StoreManagerInterface $storeManager,
        StateInterface $inlineTranslation,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        SmsSender $smsSender,
        EmailSender $emailSender,
        LoggerInterface $logger,
        TimezoneInterface $timezone
    ) {
        parent::__construct($context);
        $this->counter = $counter;
        $this->resultPageFactory = $resultPageFactory;
        $this->redemptionRepository = $redemptionRepository;
        $this->result = $result;
        $this->managerInterface = $managerInterface;
        $this->formKeyValidator  = $formKeyValidator;
        $this->counterFactory = $counterFactory;
        $this->date = $date;
        $this->redirect = $redirect;
        $this->redemptionConfig = $redemptionConfig;
        $this->storeManager = $storeManager;
        $this->inlineTranslation = $inlineTranslation;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->smsSender = $smsSender;
        $this->emailSender = $emailSender;
        $this->logger = $logger;
        $this->timezone = $timezone;
    }

    /**
     * Dispatch request
     * @return ResponseInterface|ResultInterface|void
     * @throws \Exception
     */
    public function execute()
    {
        $defaultStoreId = $this->storeManager->getStore()->getId();
        if ($this->redemptionConfig->getEnableValue('enabled', $defaultStoreId) == 0) {
            $this->messageManager->addSuccessMessage(__('The redemption module is disable by admin.'));
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl('/');
        }
        $date = $this->date->gmtDate();
        if ($this->getRequest()->getParam('confirm') == 1) {
            $counter = $this->counter->load($this->getRequest()->getParam('counter_id'));
            $counter->setStatus(Counter::STATUS_REDEMPTION);
            $counter->setRedeemDate($date);
            $counter->save();
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setPath(
                'redemption/details/complete/redemption_id/' . $counter->getRedemptionId()
            );
            return $resultRedirect;
        }
        $redemptionId = $this->getRequest()->getParam('redemption_id');
        $redemptionDetail = $this->redemptionRepository->getById($redemptionId);
        $redemptionStartDate = $redemptionDetail->getStartDate();
        $redemptionEndDate = $redemptionDetail->getEndDate();
        $currentDate = $this->changeDateFormat($date);

        if ($redemptionEndDate < $currentDate) {
            $this->messageManager->addErrorMessage(
                __('This redemption has been expired!')
            );
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl('/');
        } elseif ($redemptionStartDate > $currentDate && $redemptionEndDate > $currentDate) {
            $this->messageManager->addErrorMessage(
                __('This redemption will be available at %1', $redemptionStartDate)
            );
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setUrl('/');
        }
        $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
        if (isset($redemptionId)) {
            $redemption = $this->redemptionRepository->getById($redemptionId);
            if (empty($redemption->getData()) || $redemption->isActive() == 0) {
                $this->managerInterface->addErrorMessage(__('This redemption is not available.'));
                $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
                $resultRedirect->setUrl('/');
                return $resultRedirect;
            }
        } elseif (!isset($redemptionId)) {
            $resultRedirect = $this->result->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
        return $this->resultPageFactory->create();
    }

    /**
     * This method is used to change the date format
     * @param $date
     * @return string
     */
    private function changeDateFormat($date)
    {
        $formatedDate = '';
        try {
            $formatedDate = $this->timezone->date($date)->format(self::DATE_FORMAT);
        } catch (\Exception $exception) {
            $this->logger->debug($exception->getMessage());
        }
        return $formatedDate;
    }
}
