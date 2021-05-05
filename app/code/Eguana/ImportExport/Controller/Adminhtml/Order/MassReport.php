<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:32 PM
 */
namespace Eguana\ImportExport\Controller\Adminhtml\Order;

use Amore\CustomerRegistration\Helper\Data;
use Magento\Backend\App\Action;
use Magento\Backend\App\Action\Context;
use Magento\Backend\Model\View\Result\Redirect;
use Magento\CatalogRule\Api\CatalogRuleRepositoryInterface;
use Magento\CatalogRule\Model\ResourceModel\Rule\CollectionFactory as CatalogCollection;
use Magento\CatalogRule\Model\ResourceModel\RuleFactory;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\ResourceModel\Group\CollectionFactory as CustomerGroupCollectionFactory;
use Magento\Directory\Model\Currency;
use Magento\Framework\Api\SearchCriteriaBuilder;
use Magento\Framework\App\Filesystem\DirectoryList;
use Magento\Framework\App\Response\Http\FileFactory;
use Magento\Framework\App\ResponseInterface as ResponseInterfaceAlias;
use Magento\Framework\Controller\ResultFactory;
use Magento\Framework\Controller\ResultInterface as ResultInterfaceAlias;
use Magento\Framework\Filesystem;
use Magento\Framework\Filesystem\Directory\WriteInterface;
use Magento\Framework\Stdlib\DateTime\DateTimeFactory;
use Magento\Sales\Api\OrderAddressRepositoryInterface;
use Magento\Sales\Model\OrderFactory;
use Magento\Sales\Model\ResourceModel\Order\Grid\CollectionFactory;
use Magento\SalesRule\Api\RuleRepositoryInterface;
use Magento\Ui\Component\MassAction\Filter;
use Psr\Log\LoggerInterface;

/**
 * This class is used for Orders Reports
 *
 * Class MassReport
 */
class MassReport extends Action
{
    /**
     * @var CustomerGroupCollectionFactory
     */
    private $customerGroupCollectionFactory;

    /**
     * @var LoggerInterface
     */
    private $logger;

    /**
     * @var Currency
     */
    private $currency;

    /**
     * @var SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var Filter
     */
    private $filter;

    /**
     * @var CollectionFactory
     */
    private $collectionFactory;

    /**
     * @var WriteInterface
     */
    private $directory;

    /**
     * @var FileFactory
     */
    private $fileFactory;

    /**
     * @var OrderFactory
     */
    private $orderFactory;

    /**
     * @var CatalogRuleRepositoryInterface
     */
    private $catalogRuleRepository;

    /**
     * @var RuleRepositoryInterface
     */
    private $ruleRepository;

    /**
     * @var CatalogCollection
     */
    private $catalogCollectionFactory;

    /**
     * @var RuleFactory
     */
    private $ruleFactory;

    /**
     * @var DateTimeFactory
     */
    private $dateTimeFactory;

    /**
     * @var Data
     */
    private $customerRegistrationHelper;

    /**
     * @var OrderAddressRepositoryInterface
     */
    private $orderAddressRepository;

    /**
     * @var CustomerRepositoryInterface
     */
    private $customerRepository;

    /**
     * MassReport constructor.
     *
     * @param Context $context
     * @param RuleFactory $ruleFactory
     * @param DateTimeFactory $dateTimeFactory
     * @param CatalogRuleRepositoryInterface $catalogRuleRepository
     * @param SearchCriteriaBuilder $searchCriteriaBuilder
     * @param RuleRepositoryInterface $ruleRepository
     * @param OrderFactory $orderFactory
     * @param CatalogCollection $catalogCollectionFactory
     * @param FileFactory $fileFactory
     * @param CustomerGroupCollectionFactory $customerGroupCollectionFactory
     * @param Filesystem $filesystem
     * @param Filter $filter
     * @param Currency $currency
     * @param LoggerInterface $logger
     * @param CollectionFactory $collectionFactory
     * @param Data $customerRegistrationHelper
     * @param OrderAddressRepositoryInterface $orderAddressRepository
     * @param CustomerRepositoryInterface $customerRepository
     * @throws \Magento\Framework\Exception\FileSystemException
     */
    public function __construct(
        Context $context,
        RuleFactory $ruleFactory,
        DateTimeFactory $dateTimeFactory,
        CatalogRuleRepositoryInterface $catalogRuleRepository,
        SearchCriteriaBuilder $searchCriteriaBuilder,
        RuleRepositoryInterface $ruleRepository,
        OrderFactory $orderFactory,
        CatalogCollection $catalogCollectionFactory,
        FileFactory $fileFactory,
        CustomerGroupCollectionFactory $customerGroupCollectionFactory,
        Filesystem $filesystem,
        Filter $filter,
        Currency $currency,
        LoggerInterface $logger,
        CollectionFactory $collectionFactory,
        Data $customerRegistrationHelper,
        OrderAddressRepositoryInterface $orderAddressRepository,
        CustomerRepositoryInterface $customerRepository
    ) {
        $this->logger = $logger;
        $this->dateTimeFactory = $dateTimeFactory;
        $this->ruleFactory = $ruleFactory;
        $this->catalogCollectionFactory = $catalogCollectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->catalogRuleRepository = $catalogRuleRepository;
        $this->ruleRepository = $ruleRepository;
        $this->orderFactory = $orderFactory;
        $this->filter = $filter;
        $this->customerGroupCollectionFactory = $customerGroupCollectionFactory;
        $this->currency = $currency;
        $this->fileFactory = $fileFactory;
        $this->directory = $filesystem->getDirectoryWrite(DirectoryList::VAR_DIR);
        $this->collectionFactory = $collectionFactory;
        $this->customerRegistrationHelper = $customerRegistrationHelper;
        $this->orderAddressRepository = $orderAddressRepository;
        $this->customerRepository = $customerRepository;
        parent::__construct($context);
    }

    /**
     * Execute action to delete news
     *
     * @return Redirect|ResponseInterfaceAlias|ResultInterfaceAlias
     */
    public function execute()
    {
        $csvfilename = '';
        try {
            $collection = $this->filter->getCollection($this->collectionFactory->create());
            $order = $this->orderFactory->create();
            $collection->getSelect()->joinRight(
                ['so' => $collection->getTable('sales_order')],
                'so.entity_id = main_table.entity_id',
                ['shipping_address_id','coupon_code']
            );
            $customerGroup = $this->customerGroupCollectionFactory->create();
            $groups = [];
            foreach ($customerGroup as $group) {
                $groups[$group['customer_group_id']] = $group['customer_group_code'];
            }
            $dateModel = $this->dateTimeFactory->create();
            $name = $dateModel->gmtDate('d-m-Y H:i:s');
            $filepath = 'export/export-data-' . $name . '.csv';
            $this->directory->create('export');
            $stream = $this->directory->openFile($filepath, 'w+');
            $stream->lock();
            //column name dispay in your CSV
            $columns = ['ID','Purchase Point','Purchase Date','Member Mobile',
                'Shipping Order Mobile','Bill-to Name','Ship-to Name','Grand Total (Base)',
                'Grand Total (Purchased)','Status','Billing Address','Shipping Address',
                'Shipping Information','Customer Email','Customer Group','Subtotal',
                'Shipping and Handling','Customer Name','Payment Method','Total Refunded','Sap Response',
                'Promotion'];
            if ($this->customerRegistrationHelper->getBaCodeEnable()) {
                $columns[] = 'BA Recruiter Code';
            }
            $columns[] = 'Profit Sharing Code';
            foreach ($columns as $column) {
                $header[] = __($column);
            }
            $stream->writeCsv($header);
            foreach ($collection as $order) {
                $itemData = [];
                $itemData[] = $order->getData('increment_id');
                $itemData[] = str_replace("\n", " ", $order->getData('store_name'));
                $itemData[] = $this->getDateByFormat($order->getData('created_at'));
                $itemData[] = $this->getCustomerMobile($order->getCustomerId());
                $itemData[] = $this->getShippingAddressMobile($order->getShippingAddressId());
                $itemData[] = $order->getData('billing_name');
                $itemData[] = $order->getData('shipping_name');
                $itemData[] = $order->getData('base_grand_total');
                $itemData[] = $order->getData('grand_total');
                $itemData[] = $order->getData('status');
                $itemData[] = $order->getData('billing_address');
                $itemData[] = $order->getData('shipping_address');
                $itemData[] = $order->getData('shipping_information');
                $itemData[] = $order->getData('customer_email');
                $itemData[] = $groups[$order->getData('customer_group')];
                $itemData[] = $order->getData('subtotal');
                $itemData[] = $order->getData('shipping_and_handling');
                $itemData[] = (empty($order->getData('customer_name')) ? 'Guest' : $order->getData('customer_name'));
                $itemData[] = $order->getData('payment_method');
                $itemData[] = $order->getData('total_refunded');
                $itemData[] = $this->getSapResponse($order->getData('entity_id'));
                $itemData[] = $this->promotions($order->getData('entity_id'));
                if ($this->customerRegistrationHelper->getBaCodeEnable()) {
                    $itemData[] = $order->getData('customer_ba_code');
                }
                $itemData[] = $order->getCouponCode();

                $stream->writeCsv($itemData);
            }
            $content = [];
            $content['type'] = 'filename';
            $content['value'] = $filepath;
            $content['rm'] = '1';

            $csvfilename = 'Order-Report' . $name . '.csv';
            return $this->fileFactory->create($csvfilename, $content, DirectoryList::VAR_DIR);
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
            /** @var Redirect $resultRedirect */
            $resultRedirect = $this->resultFactory->create(ResultFactory::TYPE_REDIRECT);
            return $resultRedirect->setPath('*/*/');
        }
        return $csvfilename;
    }

    /**
     * Add currency with price
     *
     * @param $price
     * @param $currencyCode
     * @return string
     */
    private function changePrice($price, $currencyCode)
    {
        $currencyCode = isset($currencyCode) ? $currencyCode : null;
        $basePurchaseCurrency = $this->currency->load($currencyCode);
        $price = $basePurchaseCurrency->format($price, [], false);
        return $price;
    }

    /**
     * get promotion names
     *
     * @param $id
     * @return mixed|string
     */
    private function promotions($id)
    {
        $promotions = '';
        try {
            $order = $this->orderFactory->create();
            $orderData = $order->load($id);
            $order = $orderData->getData();
            $orderItems = $orderData->getAllItems();
            $cartRules = $order['applied_rule_ids'];
            $searchrecorde=$this->searchCriteriaBuilder->addFilter('rule_id', $cartRules, 'in')->create();
            $cartRules = $this->ruleRepository->getList($searchrecorde)->getItems();
            $cartRulesPromotions = '';
            foreach ($cartRules as $rule) {
                if (empty($cartRulesPromotions)) {
                    $cartRulesPromotions = $rule->getName();
                } else {
                    $cartRulesPromotions = $cartRulesPromotions . ',' . $rule->getName();
                }
            }
            $catalogRules = '';
            foreach ($orderItems as $item) {
                $store = $orderData->getStore();
                $date = $orderData->getCreatedAt();
                $websiteId = $store->getWebsiteId();
                $customerGroupId = $orderData->getCustomerGroupId();
                $productId = $item->getProductId();
                $rule = $this->ruleFactory->create();
                $ruleCollection = $rule->getRulesFromProduct($date, $websiteId, $customerGroupId, $productId);
                foreach ($ruleCollection as $rule) {
                    if (empty($catalogRules)) {
                        $catalogRules = $rule['rule_id'];
                    } else {
                        $catalogRules = $catalogRules . ',' . $rule['rule_id'];
                    }
                }
            }
            $catalogCollection = $this->catalogCollectionFactory->create();
            $catalogCollection->addFieldToSelect('name')->addFieldToSelect('rule_id')
                ->addFieldToFilter('rule_id', ['in' => $catalogRules]);
            $items = $catalogCollection->getItems();
            $catalogRulesPromotions = '';
            foreach ($items as $item) {
                if (empty($catalogRulesPromotions)) {
                    $catalogRulesPromotions = $item['name'];
                } else {
                    $catalogRulesPromotions = $catalogRulesPromotions . ',' . $item['name'];
                }
            }
            if (!empty($cartRulesPromotions) && !empty($catalogRulesPromotions)) {
                return $cartRulesPromotions . ',' . $catalogRulesPromotions;
            }
            if (!empty($cartRulesPromotions) && empty($catalogRulesPromotions)) {
                return $cartRulesPromotions;
            }
            if (empty($cartRulesPromotions) && !empty($catalogRulesPromotions)) {
                return $catalogRulesPromotions;
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $promotions;
    }

    /**
     * change date fomate
     *
     * @param $date
     * @return mixed
     */
    public function getDateByFormat($date)
    {
        $format = "M j, Y G:i:s a";
        $dateModel = $this->dateTimeFactory->create();
        return $dateModel->gmtDate($format, $date);
    }

    /**
     * get sap response
     *
     * @param $id
     * @return float|mixed|null
     */
    private function getSapResponse($id)
    {
        $orderFactory = $this->orderFactory->create();
        $orderData = $orderFactory->load($id);
        return $orderData->getData('sap_response');
    }

    /**
     * Get customer shipping address mobile no
     *
     * @param $shippingAddressId
     * @return string
     */
    private function getShippingAddressMobile($shippingAddressId)
    {
        $mobileNo = '';
        try {
            $addressInfo = $this->orderAddressRepository->get($shippingAddressId);
            $mobileNo = $addressInfo->getTelephone();
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $mobileNo;
    }

    /**
     * Get customer mobile no
     *
     * @param $customerId
     * @return string
     */
    private function getCustomerMobile($customerId)
    {
        $mobileNo = '';
        try {
            $customerInfo = $this->customerRepository->getById($customerId);
            if ($customerInfo->getCustomAttribute('mobile_number')) {
                $mobileNo = $customerInfo->getCustomAttribute('mobile_number')->getValue();
            }
        } catch (\Exception $e) {
            $this->logger->error($e->getMessage());
        }
        return $mobileNo;
    }
}
