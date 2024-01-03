<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock4 implements DataPatchInterface
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
            'title' => 'TW Laneige Spa',
            'identifier' => 'tw_laneige_spa_main',
            'content' => '<style>#html-body [data-pb-style=DBLS1MO]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=F7A6V7V],#html-body [data-pb-style=TO5CCUC]{text-align:center}#html-body [data-pb-style=W756OX6]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=WRGI3V7]{display:flex;width:100%}#html-body [data-pb-style=NU869DN]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;align-self:stretch}#html-body [data-pb-style=EH2AN06]{border-style:none}#html-body [data-pb-style=ONA46JQ],#html-body [data-pb-style=SGO5PUY]{max-width:100%;height:auto}#html-body [data-pb-style=D3R7GSX]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;align-self:stretch}#html-body [data-pb-style=G3H1E1P]{border-style:none}#html-body [data-pb-style=A7E3V4G],#html-body [data-pb-style=QS6M93J]{max-width:100%;height:auto}#html-body [data-pb-style=ON1U04K]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;align-self:stretch}#html-body [data-pb-style=C42GEWT]{border-style:none}#html-body [data-pb-style=F8AY90I],#html-body [data-pb-style=T69GTTC]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=C42GEWT],#html-body [data-pb-style=EH2AN06],#html-body [data-pb-style=G3H1E1P]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140 home-spa" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="DBLS1MO"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="TO5CCUC">《I\'m LANEIGE》雜誌</h2><div class="sub-title" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="F7A6V7V"><p style="text-align: center;"><span style="font-size: 18px;">韓系皮膚管理，保養美妝秘笈</span></p></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="W756OX6"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="WRGI3V7"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="NU869DN"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="EH2AN06"><a href="/spa/introduction.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-A_660x396_1.jpg}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="SGO5PUY"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-A_660x396_2.jpg}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="ONA46JQ"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
<p>抗老保養過程中，4個不能小看的老化原因！想要立即擁有完美新生這樣做</p>
<a href="https://tw.laneige.com/brand/new-magazine/aging.html" class="btn-viewmore">了解更多</a>
</div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="D3R7GSX"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="G3H1E1P"><a href="/spa/membership.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-B_660x396_1.png}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="A7E3V4G"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-B_660x396_2.png}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="QS6M93J"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
<p>想要擁有無瑕水嫩的無齡膚質，保濕保養程序步驟不可少！想知道你的保濕方法正不正確 </p>
<a href="https://tw.laneige.com/brand/new-magazine/recommendations-for-moisturizing-products.html" class="btn-viewmore">了解更多</a>
</div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="ON1U04K"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="C42GEWT"><a href="/spa/beauty-lounge.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-C_660x396_1.jpg}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="T69GTTC"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-C_660x396_2.jpg}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="F8AY90I"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
<p>總是妝感不服貼，浮粉？學會正確妝前保養順序，讓底妝持久完美一整天！</p>
<a href="https://tw.laneige.com/brand/new-magazine/primer.html" class="btn-viewmore">了解更多</a>
</div></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><script type="text/javascript" xml="space">
    require([\'jquery\',\'swiper\'],function($,Swiper){
        $(document).ready(function() {
            if ($(window).width() < 768) {
                if ($(\'.home-spa .pagebuilder-column-group .pagebuilder-column\').length < 2) {
                    $(\'.home-spa .pagebuilder-column-group\').addClass(\'align-items\');
                } else {
                    $(\'.home-spa .pagebuilder-column-group\').addClass(\'swiper-wrapper\');
                    $(\'.home-spa .pagebuilder-column-group .pagebuilder-column\').addClass(\'swiper-slide\');
                    $( ".home-spa" ).append(\'<div class="swiper-button-next"></div><div class="swiper-button-prev"></div>\' );
                    var swiper = new Swiper(\'.home-spa\', {
                        slidesPerView: 1.2,
                        autoHeight: true,
                        autoHeight: true,
                        spaceBetween: 12,
                        navigation: {
                            nextEl: ".swiper-button-next",
                            prevEl: ".swiper-button-prev",
                        },
                    });
                }
            }
        });

    });
</script>
<style>
    @media(max-width: 767px){
        .home-spa h2 {margin: 0 0 6px;}
        .home-spa .pagebuilder-column-group {flex-wrap: initial;}
        .home-spa .pagebuilder-column-group figure {width: 100%;}
        .home-spa {overflow: hidden;margin-left: 20px;}
        .home-spa .pagebuilder-column-group .pagebuilder-column {padding: 0 !important;flex-basis: initial;}
        .home-spa .pagebuilder-column-group [data-content-type=\'html\'] {display: block;width: 100%;}
        .home-spa .swiper-button-next {display: none;}
        .home-spa .swiper-button-prev {display: none;}
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