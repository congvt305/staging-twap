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
     * @param MagazineInterface $magazine
     * @return mixed
     */
    public function save(MagazineInterface $magazine);

    /**
     * Retrieve magazine
     * @param $magazineId
     * @return mixed
     */

    public function getById($magazineId);

    /**
     * Delete Magazine
     * @param MagazineInterface $magazine
     * @return mixed
     */
    public function delete(MagazineInterface $magazine);

    /**
     * Delete Magazine by Id
     * @param $magazineId
     * @return mixed
     */
    public function deleteById($magazineId);
}
