<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 3:30 PM
 */
namespace Eguana\NewsBoard\Api;

use Eguana\NewsBoard\Api\Data\NewsInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared CRUD
 * interface NewsRepositoryInterface
 */
interface NewsRepositoryInterface
{
    /**
     * Save news.
     *
     * @param NewsInterface $news
     * @return NewsInterface
     */
    public function save(NewsInterface $news);

    /**
     * Retrieve News.
     *
     * @param $newsId
     * @return NewsInterface
     */
    public function getById($newsId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete news.
     *
     * @param NewsInterface $news
     * @return bool true on success
     */
    public function delete(NewsInterface $news);

    /**
     * Delete news by ID.
     *
     * @param $newsId
     * @return bool true on success
     */
    public function deleteById($newsId);
}
