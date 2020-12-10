<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: bilalyounas
 * Date: 7/10/20
 * Time: 7:22 PM
 */
namespace Eguana\CustomerBulletin\Api;

use Eguana\CustomerBulletin\Api\Data\NoteInterface;
use Magento\Framework\Api\SearchCriteriaInterface;

/**
 * Declared inter
 * interface NoteRepositoryInterface
 */
interface NoteRepositoryInterface
{
    /**
     * Save note.
     *
     * @param NoteInterface $note
     * @return NoteInterface
     */
    public function save(NoteInterface $note);

    /**
     * Retrieve Notes.
     *
     * @param int $noteId
     * @return NoteInterface
     */
    public function getById($noteId);

    /**
     * Retrieve list matching the specified criteria.
     *
     * @param SearchCriteriaInterface $searchCriteria
     * @return mixed
     */
    public function getList(SearchCriteriaInterface $searchCriteria);

    /**
     * Delete note.
     *
     * @param NoteInterface $note
     * @return bool true on success
     */
    public function delete(NoteInterface $note);

    /**
     * Delete Note by ID.
     *
     * @param $noteId
     * @return bool true on success
     */
    public function deleteById($noteId);
}
