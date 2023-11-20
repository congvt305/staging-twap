<?php
/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */
namespace Sapt\Sales\Block\Returns;

/**
 * @api
 * @since 100.0.2
 */
class History extends \Magento\Rma\Block\Returns\History
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * Rma grid collection
     *
     * @var \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory
     */
    protected $_collectionFactory;

    /**
     * Customer session model
     *
     * @var \Magento\Customer\Model\Session
     */
    protected $_customerSession;

    /**
     * @param \Magento\Framework\View\Element\Template\Context $context
     * @param \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory
     * @param \Magento\Customer\Model\Session $customerSession
     * @param array $data
     */
    public function __construct(
        \Magento\Framework\View\Element\Template\Context $context,
        \Magento\Rma\Model\ResourceModel\Rma\Grid\CollectionFactory $collectionFactory,
        \Magento\Customer\Model\Session $customerSession,
        array $data = []
    ) {
        $this->_isScopePrivate = true;
        parent::__construct($context, $collectionFactory, $customerSession, $data);
    }

    /**
     * Initialize rma history content
     *
     * @return void
     */
    public function _construct()
    {
        parent::_construct();
        $this->setTemplate('Magento_Rma::return/history.phtml');

        /** @var $returns \Magento\Rma\Model\ResourceModel\Rma\Grid\Collection */
        $returns = $this->_collectionFactory->create()->addFieldToSelect(
            '*'
        )->addFieldToFilter(
            'customer_id',
            $this->_customerSession->getCustomer()->getId()
        )->setOrder(
            'date_requested',
            'desc'
        );

        if ($this->_storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            $request = $this->getRequest();
            $from = $request->getParam('from', false);
            $to = $request->getParam('to', false);
            if ($from && $to) {
                $returns->addFieldToFilter('date_requested', ['gteq' => date('Y-m-d H:i:s', strtotime($from))])
                    ->addFieldToFilter('date_requested', ['lteq' => date('Y-m-d 23:59:59', strtotime($to))]);
            }
        }

        $this->setReturns($returns);
    }
}
