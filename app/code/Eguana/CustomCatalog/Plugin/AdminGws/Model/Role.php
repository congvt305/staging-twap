<?php
/**
 * @author Eguana Team
 * @copyriht Copyright (c) 2021 Eguana {http://eguanacommerce.com}
 * Created by PhpStorm
 * User: arslan
 * Date: 4/6/21
 * Time: 9:04 PM
 */
namespace Eguana\CustomCatalog\Plugin\AdminGws\Model;

use Magento\AdminGws\Model\Role as RoleAlias;

/**
 * In this class add a before plugin to check if the $categoryPath has the null value
 * then assing an empty string
 * Class Role
 */
class Role
{
    /**
     * Check if current user have exclusive access to specified category (by path)
     * and added plugin to check if path is null then assign a empty string
     * because only null value throw exception and do not allow to add a sub category to a admin with limited resource
     * @param RoleAlias $subject
     * @param $categoryPath
     * @return array|string[]
     */
    public function beforeHasExclusiveCategoryAccess(RoleAlias $subject, $categoryPath)
    {
        if ($categoryPath == null) {
            $categoryPath = "";
        }
        return [$categoryPath];
    }
}
