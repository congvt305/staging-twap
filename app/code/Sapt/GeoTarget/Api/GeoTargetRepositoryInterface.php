<?php
namespace Sapt\GeoTarget\Api;

use Sapt\GeoTarget\Api\Data\GeoTargetInterface;
use Magento\Framework\Api\SortOrder;
use Magento\Framework\Api\SearchCriteriaInterface;

interface GeoTargetRepositoryInterface
{
    public function save(GeoTargetInterface $page);

    public function getById($id);

    public function getList(SearchCriteriaInterface $criteria);

    public function delete(GeoTargetInterface $page);

    public function deleteById($id);
}
