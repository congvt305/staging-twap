<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 30/9/20
 * Time: 06:48 PM
 */
declare(strict_types=1);

namespace Eguana\CustomerBulletin\Ui\DataProvider\CustomerBulletin\Listing;

use Eguana\CustomerBulletin\Model\ResourceModel\Ticket\Grid\CollectionFactory;
use Magento\Framework\Api\Filter;
use Magento\Framework\App\RequestInterface;
use Magento\Framework\AuthorizationInterface;
use Magento\Ui\DataProvider\AbstractDataProvider;

/**
 * Dataprovider for listing
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
    private $request;

    /**
     * DataProvider constructor.
     * @param string $name
     * @param string $primaryFieldName
     * @param string $requestFieldName
     * @param CollectionFactory $collectionFactory
     * @param RequestInterface $request
     * @param AuthorizationInterface $authorization
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
    }

    /**
     * Prepares Meta
     *
     * @return array
     */
    public function prepareMetadata()
    {
        $metadata = [];
        if (!$this->authorization->isAllowed('Eguana_CustomerBulletin::ticket_manage')) {
            $metadata = [
                'columns' => [
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
    public function getData()
    {
        $this->collection->getSelect()->joinLeft(
            ['customer' => $this->collection->getTable('customer_entity')],
            'customer.entity_id = main_table.customer_id'
        );
        $collection = $this->getCollection();
        return $collection->toArray();
    }

    /**
     * Add full text search filter to collection
     *
     * @param Filter $filter
     * @return void
     */
    public function addFilter(Filter $filter) : void
    {
        if ($filter->getField() !== 'fulltext') {
            if ($filter->getField() == 'store_id') {
                $this->collection->addFieldToFilter(
                    'main_table.' . $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
            } else {
                $this->collection->addFieldToFilter(
                    $filter->getField(),
                    [$filter->getConditionType() => $filter->getValue()]
                );
            }
        } else {
            $value = trim($filter->getValue());
            $this->collection->addFieldToFilter(
                [
                'subject','category','ticket_id','firstname'
                ],
                [
                    ['like' => $value],
                    ['like'=> $value],
                    ['like' => $value],
                    ['like'=> $value]
                     ]
            );
        }
    }
}
