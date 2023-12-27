<?php


namespace Sapt\AjaxWishlist\Plugin\Model;


use Magento\Store\Model\StoreManagerInterface;

class Wishlist
{
    const MY_SWS_STORE_CODE = 'my_sulwhasoo';
    /**
     * @var StoreManagerInterface
     */
    private $storeManager;

    public function __construct(
        StoreManagerInterface $storeManager
    )
    {
        $this->storeManager = $storeManager;
    }
    /**
     * this function to allow share the wishlist by social link
     *
     * @param \Magento\Wishlist\Model\Wishlist $subject
     * @param $method
     * @param $result
     * @return int
     */
    public function after__call(\Magento\Wishlist\Model\Wishlist $subject, $method, $result)
    {
        if ($this->storeManager->getStore()->getCode() == self::MY_SWS_STORE_CODE) {
            if ($method == 'getShared' && !$result) {
                $result = 1;
            }
        }
        return $result;
    }
}
