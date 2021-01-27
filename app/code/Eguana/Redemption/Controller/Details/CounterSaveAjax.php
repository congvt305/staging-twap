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
     * CounterSaveAjax constructor.
     * @param ResultFactory $resultFactory
     * @param Context $context
     * @param CounterFactory $counterFactory
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param CounterRepositoryInterface $counterRepository
     * @param RedemptionRepositoryInterface $redemptionRepository
     * @param SmsSender $smsSender
     * @param EmailSender $emailSender
     * @param DateTime $date
     * @param FilterBuilder $filterBuilder
     * @param FilterGroupBuilder $filterGroupBuilder
     */
    public function __construct(
        ResultFactory $resultFactory,
        Context $context,
        CounterFactory $counterFactory,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        CounterRepositoryInterface $counterRepository,
        RedemptionRepositoryInterface $redemptionRepository,
        SmsSender $smsSender,
        EmailSender $emailSender,
        DateTime $date,
        FilterBuilder $filterBuilder,
        FilterGroupBuilder $filterGroupBuilder
    ) {
        $this->resultFactory = $resultFactory;
        $this->context = $context;
        $this->counterFactory = $counterFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->counterRepository = $counterRepository;
        $this->redemptionRepository = $redemptionRepository;
        $this->smsSender = $smsSender;
        $this->emailSender = $emailSender;
        $this->date = $date;
        $this->filterBuilder = $filterBuilder;
        $this->filterGroupBuilder = $filterGroupBuilder;
        parent::__construct($context);
    }

    /**
     * This method is used to save the counter form for registration
     *
     * @return ResponseInterface|ResultInterface|void
     */
    public function execute()
    {
        if ($this->_request->isAjax()) {
            $resultJson = $this->resultFactory->create(ResultFactory::TYPE_JSON);
            $token = substr(str_shuffle('0123456789ABCDEFGHIJKLMNOPQRSTUVWXYZ'), 1, 15);
            $post = (array) $this->getRequest()->getPost();
            if (!empty($post)) {
                /** @var Counter $model */
                $date = $this->date->gmtDate();
                $model = $this->counterFactory->create();
                $model->setData('redemption_id', $post['redemption_id']);
                $model->setData('redeem_date', null);
                $model->setData('customer_name', $post['name']);
                $model->setData('email', $post['email']);
                $model->setData('telephone', $post['phone']);
                $model->setData('counter_id', $post['counter']);
                $model->setData('store_id', $post['store_id']);
                $model->setData('line_id', $post['line']);
                $model->setData('token', $token);
                $model->setData('registration_date', $date);
                $model->setData('utm_source', '');
                $model->setData('utm_medium', '');
                $model->setData('utm_content', '');

                $idCond = $this->filterBuilder->setField('secondTable.redemption_id')
                    ->setValue($post['redemption_id'])
                    ->setConditionType('eq')
                    ->create();
                $filterId = $this->filterGroupBuilder
                    ->addFilter($idCond)
                    ->create();

                $storeCond = $this->filterBuilder->setField('main_table.store_id')
                    ->setValue($post['store_id'])
                    ->setConditionType('eq')
                    ->create();
                $filterStore = $this->filterGroupBuilder
                    ->addFilter($storeCond)
                    ->create();

                $counterCond = $this->filterBuilder->setField('main_table.counter_id')
                    ->setValue($post['counter'])
                    ->setConditionType('eq')
                    ->create();
                $filterCounter = $this->filterGroupBuilder
                    ->addFilter($counterCond)
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
                $criteriaBuilder->setFilterGroups([$filterId, $filterStore, $filterCounter, $filterOr]);
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
                    if ($this->emailSender->getRegistrationEmailEnableValue($post['store_id']) == 1) {
                        try {
                            $this->emailSender->sendEmail($model->getData('entity_id'));
                            $this->smsSender->sendSms($model->getData('entity_id'));
                        } catch (\Exception $e) {
                            $this->messageManager->addErrorMessage(
                                __('Email or SMS sending failed.')
                            );
                        }
                    }
                } catch (LocalizedException $e) {
                    $this->messageManager->addErrorMessage($e->getMessage());
                } catch (\Exception $e) {
                    $this->messageManager->addExceptionMessage(
                        $e,
                        __('Something went wrong while saving the counter.')
                    );
                }
            }
            $resultJson->setData(
                [
                    "message" => __('You have successfully applied for redemption, please check your email and newsletter.'),
                    "success" => true,
                    'entity_id' => $model->getData('entity_id')
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
