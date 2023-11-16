<?php
/**
* @author Amasty Team
* @copyright Copyright (c) 2022 Amasty (https://www.amasty.com)
* @package SEO Toolkit Base for Magento 2
*/

declare(strict_types=1);

namespace Amasty\SeoToolkitLite\Api;

interface RedirectRepositoryInterface
{
    /**
     * Save
     *
     * @param \Amasty\SeoToolkitLite\Api\Data\RedirectInterface $redirect
     *
     * @return \Amasty\SeoToolkitLite\Api\Data\RedirectInterface
     */
    public function save(\Amasty\SeoToolkitLite\Api\Data\RedirectInterface $redirect);

    /**
     * Get by id
     *
     * @param int $id
     *
     * @return \Amasty\SeoToolkitLite\Api\Data\RedirectInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getById($id);

    /**
     * Delete
     *
     * @param \Amasty\SeoToolkitLite\Api\Data\RedirectInterface $redirect
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function delete(\Amasty\SeoToolkitLite\Api\Data\RedirectInterface $redirect);

    /**
     * Delete by id
     *
     * @param int $id
     *
     * @return bool true on success
     * @throws \Magento\Framework\Exception\CouldNotDeleteException
     */
    public function deleteById($id);

    /**
     * Lists
     *
     * @param \Magento\Framework\Api\SearchCriteriaInterface $searchCriteria
     *
     * @return \Magento\Framework\Api\SearchResultsInterface
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     */
    public function getList(\Magento\Framework\Api\SearchCriteriaInterface $searchCriteria);
}
