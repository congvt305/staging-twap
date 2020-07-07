<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 2:47 PM
 */

namespace Eguana\GWLogistics\Api;

use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

interface QuoteCvsLocationRepositoryInterface
{
    /**
     * @param int $locationId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $locationId): QuoteCvsLocationInterface;

    /**
     * @param QuoteCvsLocationInterface $cvsLocation
     * @return QuoteCvsLocationInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteCvsLocationInterface $cvsLocation): QuoteCvsLocationInterface;

    /**
     * @param QuoteCvsLocationInterface $cvsLocation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuoteCvsLocationInterface $cvsLocation): bool;

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * @param int $quoteAddressId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByAddressId($quoteAddressId): QuoteCvsLocationInterface;

    /**
     * @param int $quoteId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId($quoteId): QuoteCvsLocationInterface;
}
