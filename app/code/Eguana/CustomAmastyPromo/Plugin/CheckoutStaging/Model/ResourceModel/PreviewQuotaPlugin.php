<?php
/**
 * @author Eguana Team
 * @copyright Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: umer
 * Date: 5/8/20
 * Time: 6:34 PM
 */
namespace Eguana\CustomAmastyPromo\Plugin\CheckoutStaging\Model\ResourceModel;

class PreviewQuotaPlugin
{
    /**
     * Fix Magento issue with table prefix on preview
     *
     * @param \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject
     * @param callable $proceed
     * @param int $id
     *
     * @return bool
     */
    public function aroundInsert(
        \Magento\CheckoutStaging\Model\ResourceModel\PreviewQuota $subject,
        callable $proceed,
        $id
    ) {
        $connection = $subject->getConnection();
        $select = $connection->select()
            ->from($subject->getTable('quote_preview')) // Amasty fix: added getTable call
            ->where('quote_id = ?', (int) $id);
        if (!empty($connection->fetchRow($select))) {
            return true;
        }
        return 1 === $connection->insert(
            $subject->getTable('quote_preview'),
            ['quote_id' => (int) $id]
        );
    }
}
