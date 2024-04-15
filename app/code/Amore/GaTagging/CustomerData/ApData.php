<?php
declare(strict_types=1);

namespace Amore\GaTagging\CustomerData;

use Amore\GaTagging\Helper\User;
use Magento\Customer\CustomerData\SectionSourceInterface;

/**
 * Class ApData
 */
class ApData implements SectionSourceInterface
{
    /**
     * @var User
     */
    private User $userHelper;

    /**
     * @param User $userHelper
     */
    public function __construct(User $userHelper)
    {
        $this->userHelper = $userHelper;
    }

    /**
     * Get data
     *
     * @return array
     */
    public function getSectionData()
    {
        return $this->userHelper->getCustomerData();
    }
}
