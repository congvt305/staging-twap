<?php
/**
 * Created by Eguana Team.
 * User: sonia
 * Date: 6/28/20
 * Time: 1:55 PM
 */

namespace Eguana\GWLogistics\Model;

use Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterface;
use Eguana\GWLogistics\Api\QuoteCvsLocationRepositoryInterface;
use Magento\Framework\Api\SearchCriteriaInterface;
use Magento\Framework\Exception\AlreadyExistsException;
use Magento\Framework\Exception\CouldNotDeleteException;
use Magento\Framework\Exception\CouldNotSaveException;
use Magento\Framework\Exception\NoSuchEntityException;

class QuoteCvsLocationRepository implements QuoteCvsLocationRepositoryInterface
{
    /**
     * @var \Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterfaceFactory
     */
    private $quoteCvsLocationInterfaceFactory;
    /**
     * @var ResourceModel\QuoteCvsLocation
     */
    private $quoteCvsLocationResource;
    /**
     * @var ResourceModel\QuoteCvsLocation\CollectionFactory
     */
    private $collectionFactory;
    /**
     * @var \Magento\Framework\Api\SearchCriteriaBuilder
     */
    private $searchCriteriaBuilder;

    /**
     * @var \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface
     */
    private $collectionProcessor;
    /**
     * @var \Eguana\GWLogistics\Api\Data\QuoteCvsLocationSearchResultInterfaceFactory
     */
    private $quoteCvsLocationSearchResultInterfaceFactory;

    public function __construct(
        \Eguana\GWLogistics\Api\Data\QuoteCvsLocationInterfaceFactory $quoteCvsLocationInterfaceFactory,
        \Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation $quoteCvsLocationResource,
        \Eguana\GWLogistics\Model\ResourceModel\QuoteCvsLocation\CollectionFactory $collectionFactory,
        \Eguana\GWLogistics\Api\Data\QuoteCvsLocationSearchResultInterfaceFactory $quoteCvsLocationSearchResultInterfaceFactory,
        \Magento\Framework\Api\SearchCriteriaBuilder $searchCriteriaBuilder,
        \Magento\Framework\Api\SearchCriteria\CollectionProcessorInterface $collectionProcessor
    ) {
        $this->quoteCvsLocationInterfaceFactory = $quoteCvsLocationInterfaceFactory;
        $this->quoteCvsLocationResource = $quoteCvsLocationResource;
        $this->collectionFactory = $collectionFactory;
        $this->searchCriteriaBuilder = $searchCriteriaBuilder;
        $this->collectionProcessor = $collectionProcessor;
        $this->quoteCvsLocationSearchResultInterfaceFactory = $quoteCvsLocationSearchResultInterfaceFactory;
    }

    /**
     * @param int $locationId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getById(int $locationId): QuoteCvsLocationInterface
    {
        $cvsLocation = $this->quoteCvsLocationInterfaceFactory->create();

        try {
            $this->quoteCvsLocationResource->load($cvsLocation, $locationId);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Cvs location with id "%1" does not exist.', $locationId));
        }

        return $cvsLocation;
    }

    /**
     * @param QuoteCvsLocationInterface $cvsLocation
     * @return QuoteCvsLocationInterface
     * @throws CouldNotSaveException
     */
    public function save(QuoteCvsLocationInterface $cvsLocation): QuoteCvsLocationInterface
    {
        /** @var \Eguana\GWLogistics\Model\QuoteCvsLocation$cvsLocation
         */
        try {
            $this->quoteCvsLocationResource->save($cvsLocation);
            return $cvsLocation;
        } catch (\Exception $e) {
            throw new CouldNotSaveException(__('Unable to save cvs location.'), $e);
        }

    }

    /**
     * @param QuoteCvsLocationInterface $cvsLocation
     * @return bool
     * @throws CouldNotDeleteException
     */
    public function delete(QuoteCvsLocationInterface $cvsLocation): bool
    {
        /** @var \Eguana\GWLogistics\Model\QuoteCvsLocation $cvsLocation */
        try {
            $this->quoteCvsLocationResource->delete($cvsLocation);
        } catch (\Exception $e) {
            throw new CouldNotDeleteException(__('Unable to delete cvs location.'), $e);
        }

        return true;
    }

    /**
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria)
    {
        $collection = $this->collectionFactory->create();
        $this->collectionProcessor->process($searchCriteria, $collection);
        $searchResult = $this->quoteCvsLocationSearchResultInterfaceFactory->create();
        $searchResult->setSearchCriteria($searchCriteria);
        $searchResult->setTotalCount($collection->getSize());

        return $searchResult;
    }

    /**
     * @param int $quoteAddressId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByAddressId($quoteAddressId): QuoteCvsLocationInterface
    {
        $cvsLocation = $this->quoteCvsLocationInterfaceFactory->create();
        $field = 'quote_address_id';

        try {
            $this->quoteCvsLocationResource->load($cvsLocation, $quoteAddressId, $field);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Cvs location with quote address id "%1" does not exist.', $quoteAddressId));
        }

        return $cvsLocation;
    }

    /**
     * @param int $quoteId
     * @return QuoteCvsLocationInterface
     * @throws NoSuchEntityException
     */
    public function getByQuoteId($quoteId): QuoteCvsLocationInterface
    {
        $cvsLocation = $this->quoteCvsLocationInterfaceFactory->create();
        $field = 'quote_id';

        try {
            $this->quoteCvsLocationResource->load($cvsLocation, $quoteId, $field);
        } catch (\Exception $e) {
            throw new NoSuchEntityException(__('Cvs location with quote id "%1" does not exist.', $quoteId));
        }

        return $cvsLocation;
    }
}
