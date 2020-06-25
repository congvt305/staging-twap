<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Api;

use Eguana\Faq\Api\Data\FaqInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * CMS block CRUD interface.
 * @api
 * @since 100.0.2
 */
interface FaqRepositoryInterface
{
    /**
     * Save faq.
     *
     * @param FaqInterface $faq
     * @return mixed
     */
    public function save(FaqInterface $faq);

    /**
     * Retrieve faq.
     *
     * @param $faqId
     * @return mixed
     */
    public function getById($faqId);

    /**
     * Retrieve blocks matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete faq.
     *
     * @param FaqInterface $faq
     * @return bool true on success
     */
    public function delete(FaqInterface $faq);

    /**
     * Delete faq by ID.
     *
     * @param int $faqId
     * @return bool true on success
     */
    public function deleteById($faqId);
}
