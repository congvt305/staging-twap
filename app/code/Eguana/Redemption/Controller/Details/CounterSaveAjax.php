<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 26/11/20
 * Time: 4:50 PM
 */

namespace Eguana\Redemption\Controller\Details;

use Eguana\FacebookPixel\Helper\Data;
use Eguana\Redemption\Api\CounterRepositoryInterface;
use Eguana\Redemption\Api\RedemptionRepositoryInterface;
use Eguana\Redemption\Model\Counter;
use Eguana\Redemption\Model\CounterFactory;
use Eguana\Redemption\Model\Service\EmailSender;
use Eguana\Redemption\Model\Service\SmsSender;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\Api\FilterBuilder;
use Magento\Framework\Api\Search\FilterGroupBuilder;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResponseInterface;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface;
use Magento\Framework\Exception\LocalizedException;
use Eguana\Redemption\Model\RedemptionConfiguration\RedemptionConfiguration;
use Magento\Framework\Stdlib\DateTime\DateTime;

/**
 * This class is used to save the counter with Ajax Request
 *
 * Class CounterSaveAjax
 */
class CounterSaveAjax extends Action
{
    /**
     * @var ResultFactory
     */
    protected $resultFactory;

    /**
     * @var CounterFactory
     */
    private $counterFactory;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var CounterRepositoryInterface
     */
    private $counterRepository;

    /**
     * @var RedemptionRepositoryInterface
     */
    private $redemptionRepository;

    /**
     * @var SmsSender
     */
    private $smsSender;

    /**
     * @var EmailSender
     */
    private $emailSender;

    /**
     * @var DateTime
     */
    protected $date;

    /**
     * @var FilterBuilder
     */
    private $filterBuilder;

    /**
     * @var FilterGroupBuilder
     */
    private $filterGroupBuilder;

    /**
     * @var Data
     */
    private $facebookPixelHelper;

    /**
     * @var RedemptionConfiguration
     */
    private $redemptionConfig;

    /**
     * CounterSaveAjax constructor.
     * @param ResultFactory $resultFactory
     * @param Context $context
     * @param RedemptionConfiguration $redemptionConfig
     * @param CounterFactory $counterFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CounterRepositoryInterface $counterRepository
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param SmsSender $smsSender
     * @param EmailSender $emailSender
     * @param DateTime $date
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     * @param Data $facebookPixelHelper
     */
    public function __construct(
        ResultFactory                 $resultFactory,
        Context                       $context,
        RedemptionConfiguration       $redemptionConfig,
        CounterFactory                $counterFactory,
        SearchCriteriaBuilder         $searchCriteriaBuilder,
        CounterRepositoryInterface    $counterRepository,
        RedemptionRepositoryInterface $redemptionRepository,
        SmsSender                     $smsSender,
        EmailSender                   $emailSender,
        DateTime                      $date,
        FilterBuilder                 $filterBuilder,
        FilterGroupBuilder            $filterGroupBuilder,
        Data                          $facebookPixelHelper
    ) {
        $this->resultFactory = $resultFactory;
        $this->context = $context;
        $this->redemptionConfig = $redemptionConfig;
        $this->counterFactory = $counterFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->counterRepository = $counterRepository;
        $this->redemptionRepository = $redemptionRepository;
        $this->smsSender = $smsSender;
        $this->emailSender = $emailSender;
        $this->date = $date;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        $this->facebookPixelHelper = $facebookPixelHelper;
        parent::__construct($context);
    }

    /**
     * This method is used to save the counter form for registration
     *
     * @return ResponseInterface|ResultInterface|void
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 15);
            $post = (array)$this->getRequest()->getPost();
            $storeId = $post['store_id'] ?? '';
            $entity = null;
            if ($post && $storeId) {
                /** @var Counter $model */
                $date = $this->date->gmtDate();
                $individualNumber = $this->redemptionConfig->getIndividualNumber($storeId);
                $model = $this->counterFactory->create();
                $model->setData('redemption_id', $post['redemption_id']);
                $model->setData('redeem_date', null);
                $model->setData('customer_name', $post['name']);
                if (isset($post['last_name'])) {
                    $model->setData('last_name', $post['last_name']);
                }

                $model->setData('email', $post['email']);
                $model->setData('telephone', $post['phone']);
                $homeDeliveryEnabled = $this->redemptionConfig->getHomeDeliveryEnabled($storeId);
                if ($homeDeliveryEnabled) {
                    $post['counter'] = $post['counter_auto_assign'];
                    try {
                        $redemption = $this->redemptionRepository->getById($post['redemption_id']);
                        $vvipList = $redemption->getVvipList();
                        $phoneNumbers = explode(',', $vvipList);

                        if (in_array($post['phone'], $phoneNumbers)) {
                            $errorMessage = __('Thank you for your support to Xiaolanguan! You already enjoy the priority experience service of Laneige Super Member!') . '<br>'
                                . __('The number of this event is limited, please leave the opportunity to friends who have not yet challenged, thank you for your understanding!');

                            $resultJson->setData(
                                [
                                    "message" => $errorMessage,
                                    "vvip_case" => true
                                ]
                            );
                            return $resultJson;
                        }

                        $model->setData('address', $post['address']);
                    } catch (\Exception $exception) {
                    }
                }
                $model->setData('counter_id', $post['counter']);
                $model->setData('store_id', $storeId);
                if (!$homeDeliveryEnabled && isset($post['line'])) {
                    $model->setData('line_id', $post['line']);
                }
                $model->setData('token', $token);
                $model->setData('individual_number', $individualNumber);
                $model->setData('registration_date', $date);
                $model->setData('utm_source', $post['utm_source']);
                $model->setData('utm_medium', $post['utm_medium']);
                $model->setData('utm_content', $post['utm_content']);

                $idCond = $this->filterBuilder->setField('secondTable.redemption_id')
                    ->setValue($post['redemption_id'])
                    ->setConditionType('eq')
                    ->create();
                $filterId = $this->filterGroupBuilder
                    ->addFilter($idCond)
                    ->create();

                $storeCond = $this->filterBuilder->setField('main_table.store_id')
                    ->setValue($storeId)
                    ->setConditionType('eq')
                    ->create();
                $filterStore = $this->filterGroupBuilder
                    ->addFilter($storeCond)
                    ->create();

                $emailCond = $this->filterBuilder->setField('main_table.email')
                    ->setValue($post['email'])
                    ->setConditionType('eq')
                    ->create();
                $phoneCond = $this->filterBuilder->setField('main_table.telephone')
                    ->setValue($post['phone'])
                    ->setConditionType('eq')
                    ->create();
                $filterOr = $this->filterGroupBuilder
                    ->addFilter($emailCond)
                    ->addFilter($phoneCond)
                    ->create();

                $criteriaBuilder = $this->searchCriteriaBuilder;
                $criteriaBuilder->setFilterGroups([$filterId, $filterStore, $filterOr]);
                $item = $this->counterRepository->getList($criteriaBuilder->create())->getItems();

                if (!empty($item)) {
                    $resultJson->setData(
                        [
                            "message" => __('This redemption has been already completed.'),
                            "duplicate" => true
                        ]
                    );
                    return $resultJson;
                }

                try {
                    $this->counterRepository->save($model);
                    $redemptionDetail = $this->redemptionRepository->getById($post['redemption_id']);
                    $counterKey = array_search($post['counter'], $redemptionDetail->getOfflineStoreId());
                    $counterSeats = $redemptionDetail->getCounterSeats();
                    if ($counterKey !== false) {
                        $seats = $counterSeats[$counterKey];
                        $seats--;
                        $counterSeats[$counterKey] = ($seats < 0) ? 0 : $seats;
                        $redemptionDetail->setData('counter_seats', $counterSeats);
                    } else {
                        $resultJson->setData(
                            [
                                "message" => __('This redemption has been already completed.'),
                                "duplicate" => true
                            ]
                        );
                        return $resultJson;
                    }
                    $this->redemptionRepository->save($redemptionDetail);
                    if ($this->emailSender->getRegistrationEmailEnableValue($storeId) == 1) {
                        try {
                            $this->emailSender->sendEmail($model->getData('entity_id'));
                            $this->smsSender->sendSms($model->getData('entity_id'));
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(
                                __('Email or SMS sending failed.')
                            );
                        }
                    }
                    $entity = $model->getData('entity_id');
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the counter.')
                    );
                }
            }
            $isFbEnabled = $this->facebookPixelHelper->isModuleEnabled();
            $isRedemptionApplied = $this->facebookPixelHelper->isRedemptionApplied();
            $fbFunEnable = ($isFbEnabled && $isRedemptionApplied) ? true : false;
            $resultJson->setData(
                [
                    "message" => __('You have successfully applied for redemption, please check your email and newsletter.'),
                    "success" => true,
                    'fbFunEnable' => $fbFunEnable,
                    'entity_id' => $entity
                ]
            );
            return $resultJson;
        } elseif (!$this->_request->isAjax()) {
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            $resultRedirect->setUrl('/');
            return $resultRedirect;
        }
    }
}
