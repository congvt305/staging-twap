<?php
namespace Sapt\Customer\Block;

use Amore\CustomerRegistration\Model\POSSystem;
use CJ\CustomCustomer\Helper\Data;
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
use Amore\PointsIntegration\Block\Points\Index as PointsIndex;

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

    /**
     * @var PointsIndex
     */
    private $pointsIndex;
    /**
     * @var Data
     */
    private $customerHelper;

    /**
     * @var
     */
    private $posSystem;

    public function __construct(
        Template\Context $context,
        Session $customerSession,
        GroupRepositoryInterface $groupRepository,
        CollectionFactory $orderCollectionFactory,
        CouponHelper      $couponHelper,
        Json              $json,
        CustomerPointsSearch $customerPointsSearch,
        PointsIndex $pointsIndex,
        POSSystem $posSystem,
        Data $customerHelper,
        array $data = []
    ) {
        $this->customerSession = $customerSession;
        $this->groupRepository = $groupRepository;
        $this->_orderCollectionFactory = $orderCollectionFactory;
        $this->couponHelper = $couponHelper;
        $this->json = $json;
        $this->customerPointsSearch = $customerPointsSearch;
        $this->pointsIndex = $pointsIndex;
        $this->customerHelper = $customerHelper;
        $this->posSystem = $posSystem;
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

    /**
     * @return array|mixed
     */
    public function getPointsSearchResult()
    {
        return $this->pointsIndex->getPointsSearchResult();
    }

    /**
     * @param $date
     * @return string
     */
    public function dateFormat($date)
    {
        return $this->pointsIndex->dateFormat($date);
    }

    /**
     * @return string
     */
    public function getPointsUrl()
    {
        return $this->getUrl('pointsintegration/points');
    }

    /**
     * @return string
     */
    public function getReviewUrl()
    {
        return $this->getUrl('review/customer');
    }

    public function getMembershipBenefitsUrl() {
        return $this->customerHelper->getMembershipBenefitsUrl();
    }

    /**
     * Get customer data from POS
     *
     * @return array
     */
    public function getMemberInfo() {
        $customer = $this->getCustomer();
        return $this->posSystem->getMemberInfo(
            $customer->getFirstname(),
            $customer->getLastname(),
            $customer->getMobileNumber(),
            $customer->getStoreId()
        );    }
}
