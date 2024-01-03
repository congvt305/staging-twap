<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock2 implements DataPatchInterface
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
            'title' => 'TW Laneige Recommended Categories',
            'identifier' => 'tw_laneige_recommended_categories',
            'content' => '<style>#html-body [data-pb-style=O7T982M]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin-left:-15px;margin-right:-15px}#html-body [data-pb-style=QBFUHC9]{text-align:center}#html-body [data-pb-style=YQKH0KY]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=XDBKTP6]{display:flex;width:100%}#html-body [data-pb-style=ITRF80L]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=H4S56SW]{border-style:none}#html-body [data-pb-style=CFE4MDO],#html-body [data-pb-style=TY8CEQ2]{max-width:100%;height:auto}#html-body [data-pb-style=BUBFYU6]{text-align:center}#html-body [data-pb-style=CIMDSRV]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=Q19X6DV]{border-style:none}#html-body [data-pb-style=F0LCDPQ],#html-body [data-pb-style=GD8EONM]{max-width:100%;height:auto}#html-body [data-pb-style=AGSUW7A]{text-align:center}#html-body [data-pb-style=SO67BBQ]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=E93MLEJ]{border-style:none}#html-body [data-pb-style=VG0L5XU],#html-body [data-pb-style=VIPGF46]{max-width:100%;height:auto}#html-body [data-pb-style=VG5Q7JM]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=E93MLEJ],#html-body [data-pb-style=H4S56SW],#html-body [data-pb-style=Q19X6DV]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="recommended_categories margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="O7T982M"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="QBFUHC9">推薦系列</h2><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="YQKH0KY"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="XDBKTP6"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="ITRF80L"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="H4S56SW"><a href="/skincare.html" target="" data-link-type="default" title="護膚" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_A_990__1.jpg}}" alt="護膚" title="護膚" data-element="desktop_image" data-pb-style="TY8CEQ2"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_A_990__2.jpg}}" alt="護膚" title="護膚" data-element="mobile_image" data-pb-style="CFE4MDO"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="BUBFYU6">完美新生三效系列</h3></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="CIMDSRV"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="Q19X6DV"><a href="/value-set.html" target="" data-link-type="default" title="禮品套裝" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_B_990__2.jpg}}" alt="禮品套裝" title="禮品套裝" data-element="desktop_image" data-pb-style="F0LCDPQ"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_B_990__1.jpg}}" alt="禮品套裝" title="禮品套裝" data-element="mobile_image" data-pb-style="GD8EONM"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="AGSUW7A">水酷修護保濕系列</h3></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="SO67BBQ"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="E93MLEJ"><a href="{{widget type=\'Magento\Catalog\Block\Category\Widget\Link\' id_path=\'category/1031\' template=\'Magento_PageBuilder::widget/link_href.phtml\' type_name=\'Catalog Category Link\' }}" target="" data-link-type="category" title="人參系列" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_C_990__1.jpg}}" alt="人參系列" title="人參系列" data-element="desktop_image" data-pb-style="VIPGF46"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_C_990__2.jpg}}" alt="人參系列" title="人參系列" data-element="mobile_image" data-pb-style="VG0L5XU"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="VG5Q7JM">NEO型塑系列</h3></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><script type="text/javascript" xml="space">
    require([\'jquery\',\'swiper\'],function($,Swiper){
        $(document).ready(function() {
            if ($(window).width() < 768) {
                if ($(\'.recommended_categories .pagebuilder-column-group .pagebuilder-column\').length < 2) {
                    $(\'.recommended_categories .pagebuilder-column-group\').addClass(\'align-items\');
                } else {
                    $(\'.recommended_categories .pagebuilder-column-group\').addClass(\'swiper-wrapper\');
                    $(\'.recommended_categories .pagebuilder-column-group .pagebuilder-column\').addClass(\'swiper-slide\');
                    var swiper = new Swiper(\'.recommended_categories\', {
                        slidesPerView: 2.2,
                        autoHeight: true,
                        spaceBetween: 12,
                        autoplay: {
                            delay: 3500,
                            disableOnInteraction: false,
                        },
                    });
                }
            }
        });

    });
</script>
<style>
@media(max-width: 767px){
.recommended_categories {margin: 0 0 0 20px !important;}
.recommended_categories h2 {padding: 0;margin-left: 0;}
.recommended_categories .pagebuilder-column-group {flex-wrap: initial;}
.recommended_categories .pagebuilder-column-group figure {width: 100%;}
.recommended_categories {overflow: hidden;}
.recommended_categories .pagebuilder-column-group .pagebuilder-column {padding: 0 !important;flex-basis: initial;}
.recommended_categories h3 {font-size: 14px;line-height: 20px;margin: 11px 0 0;}
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