<?php

namespace Satp\AjaxWishlist\Api;

interface AjaxWishlistInterface
{
    /**
     * To Wishlist Action
     *
     * @param int $productId
     * @return mixed
     */
    public function wishlistAction($productId);
}
