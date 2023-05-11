<?php
declare(strict_types=1);

namespace Eguana\Directory\Controller\Account;

use Magento\Framework\App\Action\Context;
use Eguana\Directory\Model\ResourceModel\Ward\CollectionFactory as WardCollection;
use Magento\Framework\Json\Helper\Data as JsonData;

class GetWard extends \Magento\Framework\App\Action\Action
{
    /**
     * @var WardCollection
     */
    protected $_wardCollection;

    /**
     * @var JsonData
     */
    private $jsonHelper;

    /**
     * @param Context $context
     * @param WardCollection $wardCollection
     * @param JsonData $jsonHelper
     */
    public function __construct(
        Context $context,
        WardCollection $wardCollection,
        JsonData $jsonHelper
    ) {
        $this->jsonHelper = $jsonHelper;
        $this->_wardCollection = $wardCollection;
        parent::__construct($context);
    }

    /**
     * Change ward options
     *
     * @return \Magento\Framework\Controller\Result\Redirect
     * @SuppressWarnings(PHPMD.CyclomaticComplexity)
     */
    public function execute()
    {
        $wardData = [];
        $cityId = (int) $this->getRequest()->getParam('city_id');
        $collection = $this->_wardCollection->create();
        $collection->addFieldToSelect('ward_id')->addFieldToSelect('default_name')
            ->addFieldToFilter('city_id', $cityId)
            ->getSelect()->order('default_name asc');
        $collection->load();

        foreach ($collection as $ward) {
            $wardData[] =
                ['value' => $ward->getWardId(), 'label' => $ward->getName()];
        }

        $wardData = $this->jsonHelper->jsonEncode($wardData);
        return $this->getResponse()->setBody($wardData);
    }
}
