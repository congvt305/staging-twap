<?php
namespace Magenest\Popup\Model;

use Magento\Framework\Model\AbstractModel;

class Popup extends AbstractModel
{
    // popup type
    public const YESNO_BUTTON = 1;
    public const CONTACT_FORM = 2;
    public const SHARE_SOCIAL = 3;
    public const SUBCRIBE     = 4;
    public const STATIC_POPUP = 5;
    public const HOT_DEAL = 6;

    //popup status
    public const ENABLE  = 1;
    public const DISABLE = 0;

    //popup trigger
    public const X_SECONDS_ON_PAGE        = 1;
    public const SCROLL_PAGE_BY_Y_PERCENT = 2;
    public const VIEW_X_PAGE = 3;
    public const EXIT_INTENT = 4;

    //popup animation
    public const NONE             = 0;
    public const ZOOM             = 1;
    public const ZOOMOUT          = 2;
    public const MOVE_FROM_LEFT   = 3;
    public const MOVE_FROM_RIGHT  = 4;
    public const MOVE_FROM_TOP    = 5;
    public const MOVE_FROM_BOTTOM = 6;

    //popup position in page
    public const CENTER        = 0;
    public const TOP_LEFT      = 1;
    public const TOP_RIGHT     = 2;
    public const BOTTOM_LEFT   = 3;
    public const BOTTOM_RIGHT  = 4;
    public const MIDDLE_LEFT   = 5;
    public const MIDDLE_RIGHT  = 6;
    public const TOP_CENTER    = 7;
    public const BOTTOM_CENTER = 8;

    //popup Position
    public const ALLPAGE  = 0;
    public const HOMEPAGE = 'cms_index_index';
    public const CMSPAGE  = 'cms_page_view';
    public const CATEGORY = 'catalog_category_view';
    public const PRODUCT = 'catalog_product_view';

    // floating button positon
    public const BUTTON_CENTER       = 0;
    public const BUTTON_BOTTOM_LEFT  = 1;
    public const BUTTON_BOTTOM_RIGHT = 2;

    // floating button display popup
    public const BEFORE_CLICK_BUTTON = 0;
    public const AFTER_CLICK_BUTTON = 1;

    /**
     * @inheritDoc
     */
    public function _construct()
    {
        $this->_init(ResourceModel\Popup::class);
    }
}
