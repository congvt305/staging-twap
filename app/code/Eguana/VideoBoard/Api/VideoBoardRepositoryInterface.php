<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 11/6/20
 * Time: 11:25 AM
 */
namespace Eguana\VideoBoard\Api;

use Eguana\VideoBoard\Api\Data\VideoBoardInterface;

/**
 * Declared inter
 * interface VideoBoardRepositoryInterface
 */
interface VideoBoardRepositoryInterface
{
    /**
     * Save videoBoard.
     *
     * @param VideoBoardInterface $videoBoard
     * @return mixed
     */
    public function save(VideoBoardInterface $videoBoard);

    /**
     * Retrieve videoBoard.
     *
     * @param $videoBoardId
     * @return mixed
     */
    public function getById($videoBoardId);

    /**
     * Delete videoBoard.
     *
     * @param VideoBoardInterface $videoBoard
     * @return mixed
     */
    public function delete(VideoBoardInterface $videoBoard);

    /**
     * Delete videoBoard by ID.
     *
     * @param $videoBoardId
     * @return mixed
     */
    public function deleteById($videoBoardId);
}
