<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: yasir
 * Date: 6/16/20
 * Time: 1:52 AM
 */
namespace Eguana\Magazine\Api;

use Eguana\Magazine\Api\Data\MagazineInterface;

/**
 * Declared inter
 * interface MagazineRepositoryInterface
 */
interface MagazineRepositoryInterface
{
    /**
     * Save magazine.
     *
     * @param MagazineInterface $magazine
     * @return MagazineInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function save(MagazineInterface $magazine);

    /**
     * Retrieve magazine.
     *
     * @param int $magazineId
     * @return MagazineInterface
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function getById($magazineId);

    /**
     * Delete magazine.
     *
     * @param MagazineInterface $magazine
     * @return bool true on success
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function delete(MagazineInterface $magazine);

    /**
     * Delete magazine by ID.
     *
     * @param int $magazineId
     * @return bool true on success
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Exception\LocalizedException
     */
    public function deleteById($magazineId);
}
