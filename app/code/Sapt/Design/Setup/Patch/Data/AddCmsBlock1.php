<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock1 implements DataPatchInterface
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
            'title' => 'New Arrivals TW Laneige',
            'identifier' => 'new_arrivals_tw_laneige',
            'content' => '<style>#html-body [data-pb-style=MSGRC9O]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=NMB2TD5]{text-align:center}#html-body [data-pb-style=KVWUI55]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=MF2959S]{display:flex;width:100%}#html-body [data-pb-style=NSSKCY6]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;padding-right:10px;align-self:stretch}#html-body [data-pb-style=F7W34I6]{border-style:none}#html-body [data-pb-style=A4DWHQI],#html-body [data-pb-style=KG33HQQ]{max-width:100%;height:auto}#html-body [data-pb-style=RBXWTIA]{text-align:left}#html-body [data-pb-style=VT8MOJI]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;padding-left:10px;align-self:stretch}@media only screen and (max-width: 768px) { #html-body [data-pb-style=F7W34I6]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="new_arrivals_section margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="MSGRC9O"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="NMB2TD5">最新推薦</h2><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="KVWUI55"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="MF2959S"><div class="pagebuilder-column banner-newarrivals" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="NSSKCY6"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="F7W34I6"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-2_B_1080px_788px_1.png}}" alt="禦時緊顏參養煥白系列" title="" data-element="desktop_image" data-pb-style="KG33HQQ"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-2_B_640px_1.jpg}}" alt="禦時緊顏參養煥白系列" title="" data-element="mobile_image" data-pb-style="A4DWHQI"></figure><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="RBXWTIA"><div class="banner-posstion-left new-product-session">
<!--<h2>禦時緊顏參養<br/>煥白系列</h2>
<p>突破性專利煥白技術 緊緻透亮三層肌</p>
<h2 style="text-transform: none;">Highlights <br>of the Week</h2>-->
<h2>蘭芝ABC發光肌護膚公式​ ​</h2>
<p>早C晚A每日B 透亮細緻水潤肌</p>
<!--<p>新升級配方首次注入高效人參活膚精萃，<br>即時激活肌膚再生力。</p>-->
<a href="value-set.html" class="btn-viewmore">了解更多></a>
</div>
<style>
.new_arrivals_section .banner-posstion-left {
  top: 56px;
  left: 35px;
  transform: inherit;
}
@media only screen and (min-width: 768px) and (max-width: 991px)  {
.new_arrivals_section .pagebuilder-column-group {
    display: block !important;
}
.new_arrivals_section .pagebuilder-column-group .banner-newarrivals {
    width: 100% !important;
    padding: 0 !important;
    margin: 0 0 40px;
}
.new_arrivals_section .pagebuilder-column-group .banner-newarrivals img {
    width: 100%;
}
.new_arrivals_section .pagebuilder-column-group .product-newarrivals {
    width: 100% !important;
    padding: 0 !important;
}

}
.new_arrivals_section .banner-posstion-left.new-product-session h2, .banner-posstion-left.new-product-session p, .banner-posstion-left.new-product-session .btn-viewmore{ color: white; }
.banner-posstion-left.new-product-session h2{
   font-size: 35px;
}
@media only screen and (max-width: 767px) {
  .product-new-arrivals .swiper-button-prev {display: none !important;}
  .product-new-arrivals .swiper-button-next {display: none !important;}
  .new_arrivals_section .banner-newarrivals {padding: 0 20px !important;}
  .new_arrivals_section .product-newarrivals {padding: 0 0 0 20px !important;}
  .new_arrivals_section .banner-posstion-left.new-product-session h2, .banner-posstion-left.new-product-session p{
    color: black;
    font-size: 18px;
  }
.banner-posstion-left.new-product-session .btn-viewmore{
  color: black;
 }
}
</style></div></div><div class="pagebuilder-column product-newarrivals" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="VT8MOJI"><div class="product-new-arrivals" data-content-type="products" data-appearance="grid" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="Magento_CatalogWidget::product/widget/content/grid.phtml" anchor_text="" id_path="" show_pager="0" products_count="2" condition_option="sku" condition_option_value="simple_222,simple_111" type_name="Catalog Products List" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`simple_222,simple_111`^]^]" sort_order="position_by_sku"}}</div><div data-content-type="html" data-appearance="default" data-element="main"><script type="text/javascript" xml="space">
    require([\'jquery\',\'swiper\',\'matchMedia\'],function($,Swiper,mediaCheck){
        $(document).ready(function() {
$(window).resize(function(){
  $(\'.product-image-wrapper\').each(function() {
//console.log(\'height width\',$(this).outerHeight(),$(this).outerWidth());
    $(this).children(\'img\').height($(this).innerHeight());
$(this).children(\'img\').width($(this).innerWidth());
});
});
$(\'.product-image-wrapper\').each(function() {
//console.log(\'height width\',$(this).outerHeight(),$(this).outerWidth());
    $(this).children(\'img\').height($(this).innerHeight());
$(this).children(\'img\').width($(this).innerWidth());
});
            if ($(\'.product-new-arrivals .product-items\').offset()) {
                mediaCheck({
                    media: \'(min-width: 768px)\',
                    entry: function () {
                        if ($(\'.product-new-arrivals .product-items .product-item\').length < 4) {
                            $(\'.product-new-arrivals .product-items\').addClass(\'align-items\');
                        } else {
                            var upsellSlider = new Swiper(\'.product-new-arrivals .products-grid\', {
                                slidesPerView: 2,
                                autoHeight: true,
                                breakpoints: {
                                    767: {
                                        slidesPerView: 1.41,
                                        spaceBetween: 12,
                                    }
                                },
                                navigation: {
                                    nextEl: \'.product-new-arrivals .swiper-button-next\',
                                    prevEl: \'.product-new-arrivals .swiper-button-prev\'
                                }
                            });
                        }
                    },
                    exit: function () {
                        if ($(\'.product-new-arrivals .product-items .product-item\').length < 2) {
                            $(\'.product-new-arrivals .product-items\').addClass(\'align-items\');
                        } else {
                            var upsellSlider = new Swiper(\'.product-new-arrivals .products-grid\', {
                                slidesPerView: 2,
spaceBetween: 50,
                                autoHeight: true,
                                breakpoints: {
                                    768: {
                                        slidesPerView: 1.41,
                                        spaceBetween: 12,
                                    }
                                },
                                navigation: {
                                    nextEl: \'.product-new-arrivals .swiper-button-next\',
                                    prevEl: \'.product-new-arrivals .swiper-button-prev\'
                                }
                            });
                        }
                    }
                });
            }
        });
    });
</script></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
.new_arrivals_section .product-item {
    width: calc((100% - 20px)/2) !important;
}
@media (max-width: 767px){
.new_arrivals_section .product-item {
    width: calc((100% - 20px)/2) !important;
}
.new_arrivals_section .block-content{
padding-left: 0!important;
  padding-right: 0!important;
}
}
.new_arrivals_section .swiper-button-prev:after, .swiper-rtl .swiper-button-next:after {
    content: \'prev\';
display: none;
}
.new_arrivals_section .swiper-button-next:after, .swiper-rtl .swiper-button-prev:after {
    content: \'next\';
display: none;
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