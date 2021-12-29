<?php
namespace Eguana\Directory\Controller\Account;

use Magento\Customer\Api\AccountManagementInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Customer\Model\Session;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\ResourceConnection;
use Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory as WardCollection;


class GetWard extends \Magento\Framework\App\Action\Action
{
    protected $_wardCollection;

    /**
     * @param Context $context
     */
    public function __construct(
        Context $context,
        WardCollection $wardCollection
    ) {
        $this->_wardCollection = $wardCollection;
        parent::__construct($context);
    }

    /**
     * Change customer password action
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $cityId = (int) $this->getRequest()->getParam('city_id');
        $currentWardId = $this->getRequest()->getParam('ward_id');
        $collection = $this->_wardCollection->create();
        $collection->addFieldToSelect('ward_id')->addFieldToSelect('default_name')
            ->addFieldToFilter('city_id', $cityId)
            ->getSelect()->order('default_name asc');
        $collection->load();

        $block = $this->_view->getLayout()->createBlock('\Magento\Framework\View\Element\Template');
        $block->setTemplate('Eguana_Directory::ward_option.phtml');
        $block->setCurrentWardId($currentWardId);
        $block->setWards($collection);
        $this->getResponse()->appendBody($block->toHtml());
    }
}
