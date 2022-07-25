<?php
namespace Satp\Customer\Block;

use Magento\Customer\Api\Data\GroupInterface;
use Magento\Customer\Api\GroupRepositoryInterface;
use Magento\Customer\Model\Customer;
use Magento\Customer\Model\Session;
use Magento\Framework\Exception\LocalizedException;
use Magento\Framework\Exception\NoSuchEntityException;
use Magento\Framework\View\Element\Template;
use Magento\Framework\Serialize\Serializer\Json;
use Magento\Sales\Model\ResourceModel\Order\CollectionFactory;
use CJ\CouponCustomer\Helper\Data as CouponHelper;
use Amore\PointsIntegration\Model\CustomerPointsSearch;

class Membership extends \CJ\Review\Block\Top\Info
{
    /**
     * @var Session
     */
    protected $customerSession;

    /**
     * @var GroupRepositoryInterface
     */
    protected $groupRepository;

    /**
     * @var CustomerPointsSearch
     */
    private $customerPointsSearch;


    public function __construct(
        Template\Context $context,
        Session $customerSession,
        GroupRepositoryInterface $groupRepository,
        CollectionFactory $orderCollectionFactory,
        CouponHelper      $couponHelper,
        Json              $json,
        CustomerPointsSearch $customerPointsSearch,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->couponHelper = $couponHelper;
        $this->json = $json;
        $this->customerPointsSearch = $customerPointsSearch;

        parent::__construct(
            $context,
            $orderCollectionFactory,
            $customerSession,
            $couponHelper,
            $json,
            $customerPointsSearch,
            $data
        );
    }

    /**
     * @param $groupId
     * @return GroupInterface|string
     */
    public function getGroupName($groupId)
    {
        try {
            return $this->groupRepository->getById($groupId);
        } catch (NoSuchEntityException $e) {
        } catch (LocalizedException $e) {
        }
        return '';
    }

    /**
     * @return \Magento\Sales\Model\ResourceModel\Order\Collection
     */
    private function getOrderCollection(): \Magento\Sales\Model\ResourceModel\Order\Collection
    {
        return $this->_orderCollectionFactory->create()
            ->addAttributeToSelect('status')
            ->addFieldToFilter('customer_id', $this->getCustomer()->getId());
    }
}
