<?php
    namespace Hoolah\Hoolah\Helper;

    use \Magento\Framework\App\Helper\AbstractHelper;

    use \Hoolah\Hoolah\Controller\Main as HoolahMain;
    use \Hoolah\Hoolah\Helper\API as HoolahAPI;

    class Order extends AbstractHelper
    {
        // private
        protected $hdata;
        protected $hlog;
        protected $quoteRepository;
        protected $orderCollectionFactory;
        protected $invoiceService;
        protected $dbTransaction;
        protected $transactionBuilder;
        protected $orderNotifier;
        protected $quoteManagement;
        protected $invoiceNotifier;
        protected $searchCriteriaBuilder;
        protected $orderRepository;

        // public
        public function __construct(
            \Magento\Framework\App\Helper\Context $context,
            \Hoolah\Hoolah\Helper\Data $hdata,
            \Hoolah\Hoolah\Helper\Log $hlog,
            \Magento\Quote\Model\QuoteRepository $quoteRepository,
            \Magento\Sales\Model\ResourceModel\Order\CollectionFactory $orderCollectionFactory,
            \Magento\Sales\Model\Service\InvoiceService $invoiceService,
            \Magento\Framework\DB\Transaction $dbTransaction,
            \Magento\Sales\Model\Order\Payment\Transaction\Builder $transactionBuilder,
            \Magento\Sales\Model\OrderNotifier $orderNotifier,
            \Magento\Quote\Model\QuoteManagement $quoteManagement,
            \Magento\Sales\Model\Order\InvoiceNotifier $invoiceNotifier,
            \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
            \Magento\Sales\Api\OrderRepositoryInterface $orderRepository
        )
        {
            parent::__construct($context);

            $this->hdata = $hdata;
            $this->hlog = $hlog;
            $this->quoteRepository = $quoteRepository;
            $this->orderCollectionFactory = $orderCollectionFactory;
            $this->invoiceService = $invoiceService;
            $this->dbTransaction = $dbTransaction;
            $this->transactionBuilder = $transactionBuilder;
            $this->orderNotifier = $orderNotifier;
            $this->quoteManagement = $quoteManagement;
            $this->invoiceNotifier = $invoiceNotifier;
            $this->searchCriteriaBuilder = $searchCriteriaBuilder;
            $this->orderRepository = $orderRepository;
        }

        public function _updateStateFromHoolah($quote_id)
        {
            try
            {
                $this->hlog->notice('update started for quote '.$quote_id);
                if (!$quote_id)
                {
                    $this->hlog->notice('quote id is incorrect');
                    return false;
                }

                $quote = $this->quoteRepository->get($quote_id);
                if (!$quote)
                {
                    $this->hlog->notice('quote id is incorrect');
                    return false;
                }

                $this->hlog->notice('data from quote hoolah_order_ref = '.$quote->getHoolahOrderRef().', hoolah_order_context_token = '.$quote->getHoolahOrderContextToken());
                if (!$quote->getHoolahOrderRef())
                    return false;

                $store = $quote->getStore();

                if ($this->hdata->credentials_are_empty($store))
                {
                    $this->hlog->notice('merchant credentials are empty');
                    return false;
                }

                $api = new HoolahAPI(
                    $this->hdata->get_merchant_id($store),
                    $this->hdata->get_merchant_secret($store),
                    $this->hdata->get_hoolah_url($store)
                );

                $response = $api->merchant_order_get($quote->getHoolahOrderRef());
                $this->hlog->notice('got data from hoolah', $response);
                if (!HoolahAPI::is_200($response))
                    return false;

                $order = null;
                $collection = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id);
                if ($collection->count())
                    $order = $collection->getLastItem();

                $this->hlog->notice('status is '.$response['body']['status']);
                if ($response['body']['status'] == 'APPROVED')
                {
                    $data = $response['body'];

                    $uuid = $quote->getHoolahOrderRef();
                    if (isset($data['uuid']))
                        $uuid = $data['uuid'];
                    else if (isset($data['orderUuid']))
                        $uuid = $data['orderUuid'];

                    if (!$order || $order->getState() == \Magento\Sales\Model\Order::STATE_CANCELED)
                    {
                        $this->hlog->notice('order is absent or cancelled, so create a new one');

                        if (!$this->createOrder($quote))
                            return false;
                        $order = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id)->getLastItem();
                    }

                    // checking, if it's a correct order
                    if ($order->getHoolahOrderContextToken() != $quote->getHoolahOrderContextToken())
                    {
                        $this->hlog->notice('order isn\'t our, so create a new one');

                        if (!$this->createOrder($quote))
                            return false;
                        $order = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id)->getLastItem();
                    }

                    $this->hlog->notice('got order '.$order->getEntityId().' / '.$order->getIncrementId());
                    if ($order->getStatus() != 'pending')
                        $this->hlog->notice('order is not pending (it is '.$order->getStatus().')');
                    else
                    {
                        $this->hlog->notice('payment completed successfully');

                        if ($uuid)
                        {
                            $order->addStatusHistoryComment('Payment completed successfully with order_uuid = '.$uuid);
                            $order->setHoolahOrderRef($uuid);
                        }
                        else
                            $order->addStatusHistoryComment('Payment completed successfully');
                        $order->setState($this->hdata->getOrderStatus())->setStatus($this->hdata->getOrderStatus());
                        $order->save();

                        if ($order->canInvoice())
                        {
                            $invoice = $this->invoiceService->prepareInvoice($order);
                            $invoice->setState(\Magento\Sales\Model\Order\Invoice::STATE_PAID);
                            if ($uuid)
                                $invoice->setTransactionId('hoolah_'.$uuid);
                            $invoice->register();
                            $invoice->getOrder()->setIsInProcess(true);
                            $invoice->pay();

                            $transactionSave = $this->dbTransaction->addObject(
                                $invoice
                            )->addObject(
                                $invoice->getOrder()
                            );
                            $transactionSave->save();

                            $order->setTotalPaid($order->getTotalPaid());
                            $order->setBaseTotalPaid($order->getBaseTotalPaid());

                            //get payment object from order object
                            $payment = $order->getPayment();
                            $payment->setLastTransId($uuid);
                            $payment->setTransactionId('hoolah_'.$uuid);
                            $payment->setAdditionalInformation(
                                [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $data]
                            );
                            $formatedPrice = $order->getBaseCurrency()->formatTxt(
                                $order->getGrandTotal()
                            );

                            $message = __('The authorized amount is %1.', $formatedPrice);

                            $transaction = $this->transactionBuilder->setPayment($payment)
                                ->setOrder($order)
                                ->setTransactionId('hoolah_'.$uuid)
                                ->setAdditionalInformation(
                                    [\Magento\Sales\Model\Order\Payment\Transaction::RAW_DETAILS => (array) $data]
                                )
                                ->setFailSafe(true)
                                ->build(\Magento\Sales\Model\Order\Payment\Transaction::TYPE_CAPTURE);

                            $payment->addTransactionCommentsToOrder(
                                $transaction,
                                $message
                            );
                            $payment->setParentTransactionId(null);

                            $payment->save();
                            $order->save();
                            $invoice->save();
                            $transaction->save();

                            $this->orderNotifier->notify($order);
                            if ($this->hdata->get_create_email_notification())
                                $this->invoiceNotifier->notify($invoice);
                        }
                    }

                    // finalize
                    //$this->hlog->notice('hoolah_update: finalizing - '.$uuid.' - '.$quote_id.' - '.$order->getIncrementId().' - '.$order->getCustomerEmail().'...');
                    $this->hlog->notice('patching - '.$uuid.' - '.$quote_id.' - '.$order->getIncrementId().' - '.$order->getCustomerEmail().'...');

                    //$response = $api->merchant_order_finalize($uuid, $quote_id, $order->getIncrementId(), $order->getCustomerEmail());
                    $response = $api->merchant_order_patch_order_id($uuid, $order->getIncrementId());
                    if (!HoolahAPI::is_200($response))
                        $this->hlog->notice('... was FAILED', $response);
                    else
                        $this->hlog->notice('... was SUCCEEDED', $response);

                    return true;
                }
                else if ($response['body']['status'] == 'REJECTED')
                {
                    $this->hlog->notice('payment is failed');

                    if ($quote)
                    {
                        $quote->setHoolahUpdateAttempts(999);
                        $quote->save();
                    }

                    if ($order)
                    {
                        $order->addStatusHistoryComment('Payment FAILED');
                        $order->save();
                    }
                }
                else
                    return null;
            }
            catch (\Throwable $e)
            {
                $message = $e->getMessage();

                $this->hlog->error('some exception: '.$message);
            }

            return false;
        }

        public function updateStateFromHoolah($quote_id)
        {
            $result = false;

            $fp = null;
            try
            {
                $this->hlog->notice('trying update state for quote '.$quote_id);

                if ($quote_id)
                {
                    $filename = sys_get_temp_dir().'/hoolah_quote_'.$quote_id.'.lock';
                    $fp = @fopen($filename, 'w+');
                }

                if (!$fp || flock($fp, LOCK_EX | LOCK_NB)) // file lock
                {
                    $quote = $this->quoteRepository->get($quote_id);

                    $quote->setHoolahUpdateAttempts($quote->getHoolahUpdateAttempts() + 1);
                    $quote->save();

                    if (
                        !$quote->getHoolahUpdateStartedAt() ||
                        $quote->getHoolahUpdateFinishedAt() ||
                        strtotime($quote->getHoolahUpdateStartedAt()) < time() - 60*3
                    )
                    {
                        $quote->setHoolahUpdateStartedAt(time());
                        $quote->setHoolahUpdateFinishedAt(null);
                        $quote->save();

                        $result = $this->_updateStateFromHoolah($quote_id);

                        $quote->setHoolahUpdateFinishedAt(time());
                        $quote->save();
                    }
                    else
                    {
                        $this->hlog->notice('update already running for quote '.$quote_id);
                        $result = null;
                    }

                    if ($fp)
                        @flock($fp, LOCK_UN);
                }
                else
                {
                    $this->hlog->notice('update already running for quote '.$quote_id);
                    $result = null;
                }
            }
            finally
            {
                if ($fp)
                    @fclose($fp);
            }

            return $result;
        }

        public function prepareSessionForThankyou($quote_id, $checkoutSession)
        {
            $order = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id)->getLastItem();
            if ($order && $order->getEntityId())
                $checkoutSession
                    ->setLastSuccessQuoteId($quote_id)
                    ->setLastQuoteId($quote_id)
                    ->setLastOrderId($order->getId())
                    ->setLastRealOrderId($order->getIncrementId());
        }

        public function closePayment($quote_id)
        {
            $this->hlog->notice('closing an order for quote '.$quote_id);
            $order = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id)->getLastItem();
            if ($order && $order->getEntityId())
            {
                $this->hlog->notice('got order '.$order->getEntityId().' / '.$order->getIncrementId());
                $this->hlog->notice('data from the order hoolah_order_ref = '.$order->getHoolahOrderRef().', hoolah_order_context_token = '.$order->getHoolahOrderContextToken());

                if (
                    $order->getId() &&
                    $order->getState() != \Magento\Sales\Model\Order::STATE_CANCELED &&
                    $order->getState() != $this->hdata->getOrderStatus()
                )
                {
                    $order->registerCancellation('Payment was closed')->save();
                    $this->hlog->notice('order was closed');

                    return true;
                }
                else
                    $this->hlog->notice('cant close - order is '.$order->getState());
            }

            return false;
        }

        public function restoreQuote($quote_id, $checkoutSession)
        {
            $this->hlog->notice('restoring the quote '.$quote_id);
            $quote = $this->quoteRepository->get($quote_id);
            $quote->setIsActive(1)->setReservedOrderId(null);
            $this->quoteRepository->save($quote);
            $quote->save();

            $checkoutSession->replaceQuote($quote)->unsLastRealOrderId();

            $order = $this->orderCollectionFactory->create()->addFieldToFilter('quote_id', $quote_id)->getLastItem();
            $this->_eventManager->dispatch('restore_quote', ['order' => $order, 'quote' => $quote]);

            $this->hlog->notice('quote was restored '.$quote_id);
        }

        public function createOrder($quote)
        {
            $this->hlog->notice('creating an order for the quote '.$quote->getEntityId());

            try
            {
                // creating an order
                if (!$quote->getCustomerId())
                {
                    $quote->setCustomerId(null)
                        ->setCustomerEmail($quote->getBillingAddress()->getEmail())
                        ->setCustomerIsGuest(true)
                        ->setCustomerGroupId(\Magento\Customer\Api\Data\GroupInterface::NOT_LOGGED_IN_ID);
                }

                //Customize here, add customer name to quote
                $billingAddress = $quote->getBillingAddress();
                if ($quote->getCustomerFirstname() === null
                    && $quote->getCustomerLastname() === null
                    && $billingAddress
                ) {
                    $quote->setCustomerFirstname($billingAddress->getFirstname());
                    $quote->setCustomerLastname($billingAddress->getLastname());
                    if ($billingAddress->getMiddlename() === null) {
                        $quote->setCustomerMiddlename($billingAddress->getMiddlename());
                    }
                }
                //End customize

                $quote->setPaymentMethod('hoolah');
                $quote->setInventoryProcessed(false);
                $quote->save();

                $quote->getPayment()->importData(['method' => 'hoolah']);

                $order = $this->quoteManagement->submit($quote);
                if (!$order)
                {
                    $this->hlog->notice('hoolah: order creation error');
                    return false;
                }

                $result['order_id'] = intval($order->getEntityId());
                $this->hlog->notice('hoolah: order created - '.$order->getEntityId());

                $order->setHoolahOrderRef($quote->getHoolahOrderRef());
                $order->setHoolahOrderContextToken($quote->getHoolahOrderContextToken());
                $order->save();
                // end creating an order

                $this->hlog->notice('got order '.$order->getEntityId().' / '.$order->getIncrementId());
                $this->hlog->notice('data from the order hoolah_order_ref = '.$order->getHoolahOrderRef().', hoolah_order_context_token = '.$order->getHoolahOrderContextToken());
            }
            catch (\Throwable $e)
            {
                $message = $e->getMessage();

                $this->hlog->error('some exception: '.$message);

                return false;
            }

            return true;
        }

        public function cron()
        {
            $result = [
                'possible' => 0,
                'done' => 0
            ];

            HoolahMain::load_configs();

            // quotes
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('is_active', '1')
                ->addFilter('hoolah_order_context_token', null, 'notnull')
                ->addFilter('hoolah_order_ref', null, 'notnull')
                ->addFilter('updated_at', date('Y-m-d', time() - 60*60*24), 'gt')
                ->addFilter('hoolah_update_attempts', 999, 'lt')
                ->create();
            $quotes = $this->quoteRepository->getList($searchCriteria);

            if ($quotes->getTotalCount())
            {
                $result['possible'] = $quotes->getTotalCount();
                $this->hlog->notice('cron orders updater: '.$quotes->getTotalCount().' possible quotes');

                foreach ($quotes->getItems() as $quote)
                    if ($this->updateStateFromHoolah($quote->getEntityId()))
                        $result['done']++;
            }

            // orders
            $searchCriteria = $this->searchCriteriaBuilder
                ->addFilter('state', 'new')
                ->addFilter('status', 'pending')
                ->addFilter('hoolah_order_context_token', null, 'notnull')
                ->addFilter('updated_at', date('Y-m-d', time() - 60*60*24), 'gt')
                ->create();
            $orders = $this->orderRepository->getList($searchCriteria);

            if ($orders->getTotalCount())
            {
                $result['possible'] = $orders->getTotalCount() + @$result['possible'];
                $this->hlog->notice('cron orders updater: '.$orders->getTotalCount().' possible orders');

                foreach ($orders->getItems() as $order)
                    if ($this->updateStateFromHoolah($order->getQuoteId()))
                        $result['done']++;
            }

            return $result;
        }
    }
