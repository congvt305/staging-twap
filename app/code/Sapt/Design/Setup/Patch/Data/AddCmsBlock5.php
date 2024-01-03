<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock5 implements DataPatchInterface
{
    /**
     * @param BlockFactory $blockFactory
     */
    public function __construct(
        BlockFactory $blockFactory
    ) {
        $this->blockFactory = $blockFactory;
    }
    /**
     * @inheritdoc
     */
    public function apply()
    {
        $cmsBlockData = [
            'title' => 'TW Laneige Membership Only Home',
            'identifier' => 'tw_laneige_membership_only_home',
            'content' => '<style>#html-body [data-pb-style=D1I944I],#html-body [data-pb-style=K4A7FU2]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=K4A7FU2]{justify-content:flex-start;display:flex;flex-direction:column;background-color:#ddd7cc;margin-top:140px;padding-top:80px;padding-bottom:80px}#html-body [data-pb-style=D1I944I]{align-self:stretch}#html-body [data-pb-style=NWN0RQM]{display:flex;width:100%}#html-body [data-pb-style=KCU5S2C]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:41.6667%;padding-left:80px;align-self:stretch}#html-body [data-pb-style=PS5EJ5N]{display:none}#html-body [data-pb-style=V8B4069]{display:inline-block}#html-body [data-pb-style=P9OSHPR]{text-align:center;margin-top:40px}#html-body [data-pb-style=TGYV87D]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:58.3333%;align-self:stretch}</style><div class="home-membership" data-content-type="row" data-appearance="full-width" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="K4A7FU2"><div class="row-full-width-inner" data-element="inner"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="D1I944I"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="NWN0RQM"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="KCU5S2C"><h2 data-content-type="heading" data-appearance="default" data-element="main">會員獨享 </h2><div data-content-type="text" data-appearance="default" data-element="main"><p><span style="font-size: 18px;">成為台灣蘭芝會員，優先獲取品牌最新消息，並享有會員禮遇。</span></p></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-view" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="KDMPHYE"><a class="membership-link pagebuilder-button-secondary" href="https://tw.laneige.com/special-offers/membership.html" target="" data-link-type="default" data-element="link" data-pb-style="K5BE2Y6"><span data-element="link_text">了解更多</span></a></div></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="PS5EJ5N"><div class="btn-view" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="V8B4069"><a class="pagebuilder-button-secondary" href="membership/online-shop-exclusive-privileges.html" target="" data-link-type="default" data-element="link" data-pb-style="P9OSHPR"><span data-element="link_text">了解更多</span></a></div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="TGYV87D"><div data-content-type="html" data-appearance="default" data-element="main"><div class="membership-items">
    <div class="membership-item">
        <img src="{{media url=wysiwyg/laneige/80px_Welcome_gift.png}}" alt="Welcome Gift" />
        <h3>入會好禮</h3>
    </div>
    <div class="membership-item">
        <img src="{{media url=wysiwyg/laneige/80px_Sample_Product.png}}" alt="Sample Product" />
        <h3>每月體驗禮</h3>
    </div>
    <div class="membership-item">
        <img src="{{media url=wysiwyg/laneige/80px_membership_Point.png}}" alt="Membership Point" />
        <h3>會員點數</h3>
    </div>
</div></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
.membership-item img {
   max-width:80px;
}
.home-membership{
  background-color: #c9e1ff !important;
}
@media(max-width: 767px){
.home-membership [data-content-type=\'text\'] span {font-size: 13px !important;line-height: 19px;letter-spacing: -0.26px;}
.home-membership .pagebuilder-column .btn-view {position: absolute;bottom: 40px;left: 20px;}
.home-membership .pagebuilder-column .btn-view {position: absolute;bottom: 40px;left: 20px;right: 20px;text-align: center;}
.home-membership .pagebuilder-column .btn-view .pagebuilder-button-secondary {min-width: 240px;}
#html-body .home-membership .pagebuilder-column {padding-left:20px;}
}
@media(max-width: 480px){
.home-membership .pagebuilder-column .btn-view .pagebuilder-button-secondary {min-width: 120px;}
}
.membership-link{
text-align: center;
    margin-top: 40px !important;
}
</style></div></div></div>',
            'is_active' => 1,
            'stores' => [4],
            'sort_order' => 0
        ];
        $this->blockFactory->create()->setData($cmsBlockData)->save();
    }
    /**
     * @inheritdoc
     */
    public static function getDependencies()
    {
        return [];
    }
    /**
     * @inheritdoc
     */
    public function getAliases()
    {
        return [];
    }
}