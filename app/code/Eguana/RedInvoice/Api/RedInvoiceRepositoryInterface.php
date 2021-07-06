<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 5/11/21
 * Time: 10:10 AM
 */
namespace Eguana\RedInvoice\Api;

use Eguana\RedInvoice\Api\Data\RedInvoiceInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Interface RedInvoiceRepositoryInterface
 * @api
 */
interface RedInvoiceRepositoryInterface
{
    /**
     * Save RedInvoice
     *
     * @param $redInvoice
     * @return RedInvoiceInterface
     */
    public function save($redInvoice);

    /**
     * Retrieve RedInvoice
     *
     * @param int $redInvoiceId
     * @return RedInvoiceInterface
     */
    public function getById($redInvoiceId);

    /**
     * Retrieve RedInvoice matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete RedInvoice
     *
     * @param $redInvoice
     * @return bool true on success
     */
    public function delete($redInvoice);

    /**
     * Delete RedInvoice by Id
     *
     * @param int $redInvoiceId
     * @return bool true on success
     */
    public function deleteById($redInvoiceId);
}
