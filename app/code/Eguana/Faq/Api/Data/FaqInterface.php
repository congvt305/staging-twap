<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2020 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 17/6/20
 * Time: 08:00 PM
 */
namespace Eguana\Faq\Api\Data;

/**
 * interface FaqInterface
 * @api
 */
interface FaqInterface
{
    /**
     * Constant
     */
    const ENTITY_ID = 'entity_id';

    const TITLE = 'title';

    const CATEGORY = 'category';

    const DESCRIPTION = 'description';

    const CREATED_AT = 'created_at';

    const UPDATED_AT = 'updated_at';

    const IS_ACTIVE = 'is_active';

    /**
     * @param int $entity_id
     * @return $this
     */
    public function setEntityId($entity_id);

    /**
     * @return int
     */
    public function getEntityId();

    /**
     * @param int $category
     * @return $this
     */
    public function setCategory($category);

    /**
     * @return int
     */
    public function getCategory();

    /**
     * @param string $title
     * @return $this
     */
    public function setTitle($title);

    /**
     * @return string
     */
    public function getTitle();

    /**
     * @param string $description
     * @return $this
     */
    public function setDescription($description);

    /**
     * @return string
     */
    public function getDescription();

    /**
     * @param string $createdAt
     * @return $this
     */
    public function setCreatedAt($createdAt);

    /**
     * @return string
     */
    public function getCreatedAt();

    /**
     * @param string $updatedAt
     * @return $this
     */
    public function setUpdatedAt($updatedAt);

    /**
     * @return string
     */
    public function getUpdatedAt();

    /**
     * @param string $isActive
     * @return $this
     */
    public function setIsActive($isActive);

    /**
     * @return string
     */
    public function getIsActive();
}
