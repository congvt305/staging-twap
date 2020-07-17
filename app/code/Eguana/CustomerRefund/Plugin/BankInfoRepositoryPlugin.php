<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 7/17/20
 * Time: 9:14 AM
 */

namespace Eguana\CustomerRefund\Plugin;


use Eguana\CustomerRefund\Api\BankInfoRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

class BankInfoRepositoryPlugin
{
    /**
     * @var \Eguana\CustomerRefund\Model\Cryptographer
     */
    private $cryptographer;

    public function __construct(\Eguana\CustomerRefund\Model\Cryptographer $cryptographer)
    {
        $this->cryptographer = $cryptographer;
    }

    /**
     * @param \Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $subject
     * @param \Eguana\CustomerRefund\Api\Data\BankInfoSearchResultInterface $result
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function afterGetList(\Eguana\CustomerRefund\Api\BankInfoRepositoryInterface $subject, $result, SearchCriteriaInterface $searchCriteria)
    {

        $items = $result->getItems();
        foreach ($items as $item) {
            if ($item->getBankAccountNumber() && $item->getData('base64iv')) {
                $decryptData = $this->cryptographer->decode($item->getBankAccountNumber(), $item->getData('base64iv'));
                $item->setBankAccountNumber($decryptData);
            }
        }
        return $result->setItems($items);
    }
}
