<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 28/10/20
 * Time: 2:11 PM
 */
namespace Eguana\Redemption\Ui\Component\DataProvider\Redemption\CounterListing;

use Eguana\Redemption\Model\ResourceModel\Counter\Grid\Collection;
use Eguana\Redemption\Model\ResourceModel\Counter\Grid\CollectionFactory;
use Eguana\StoreLocator\Api\StoreInfoRepositoryInterface;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;
use Magento\Framework\Api\Filter;

/**
 * Data Provider For Listing
 *
 * Class DataProvider
 */
class DataProvider extends AbstractDataProvider
{
    /**
     * @var AuthorizationInterface
     */
    private $authorization;

    /**
     * @var RequestInterface
     */
    protected $request;

    /**
     * @var StoreInfoRepositoryInterface
     */
    private $storeInfoRepository;

    /**
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param AuthorizationInterface $authorization
     * @param StoreInfoRepositoryInterface $storeInfoRepository
     * @param array $meta
     * @param array $data
     */
    public function __construct(
        $name,
        $primaryFieldName,
        $requestFieldName,
        CollectionFactory $collectionFactory,
        RequestInterface $request,
        AuthorizationInterface $authorization,
        StoreInfoRepositoryInterface $storeInfoRepository,
        array $meta = [],
        array $data = []
    ) {
        parent::__construct(
            $name,
            $primaryFieldName,
            $requestFieldName,
            $meta,
            $data
        );

        $this->request          = $request;
        $this->collection       = $collectionFactory->create();
        $this->authorization    = $authorization;
        $this->storeInfoRepository = $storeInfoRepository;
    }

    /**
     * Prepares Meta
     *
     * @return array
     */
    public function prepareMetadata()
    {
        $metadata = [];
        if (!$this->authorization->isAllowed('Eguana_Redemption::redemption')) {
            $metadata = [
                'event_columns' => [
                    'arguments' => [
                        'data' => [
                            'config' => [
                                'editorConfig' => [
                                    'enabled' => false
                                ]
                            ]
                        ]
                    ]
                ]
            ];
        }
        return $metadata;
    }

    /**
     * Get grid data
     *
     * @return array
     */
    public function getData(): array
    {
        /** @var Collection $collection */
        $collection = $this->getCollection();

        $data['items'] = [];
        if ($this->request->getParam('redemption_id')) {
            $collection->addFieldToFilter('main_table.redemption_id', $this->request->getParam('redemption_id'));
            foreach ($collection as $coll) {
                $coll['counter_id'] = $this->storeInfoRepository->getById($coll['counter_id'])->getTitle();
            }
            $data = $collection->toArray();
        }
        return $data;
    }

    /**
     * Add full text search filter to collection
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter)
    {
        if ($filter->getField() !== 'fulltext') {
            $this->collection->addFieldToFilter(
                $filter->getField(),
                [$filter->getConditionType() => $filter->getValue()]
            );
        } else {
            $value = trim($filter->getValue());
            $this->collection->addFieldToFilter(
                [
                    ['attribute' => 'customer_name'],
                    ['attribute' => 'redeem_date'],
                    ['attribute' => 'email'],
                    ['attribute' => 'telephone'],
                    ['attribute' => 'registration_date'],
                ],
                [
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"],
                    ['like' => "%{$value}%"]
                ]
            );
        }
    }
}
