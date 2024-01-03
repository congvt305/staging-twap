<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock3 implements DataPatchInterface
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
            'title' => 'TW Laneige Post Banner',
            'identifier' => 'tw-laneige-main-post',
            'content' => '<style>#html-body [data-pb-style=MLYHOSJ],#html-body [data-pb-style=VLC0WVS]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=MLYHOSJ]{justify-content:flex-start;display:flex;flex-direction:column;margin:0;padding:0 0 70px}#html-body [data-pb-style=VLC0WVS]{align-self:stretch}#html-body [data-pb-style=RQ7IA4U]{display:flex;width:100%}#html-body [data-pb-style=FEKLQLS]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;text-align:right;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=G9QQ93F],#html-body [data-pb-style=KS0YCW1],#html-body [data-pb-style=R3KKCQV]{text-align:center}#html-body [data-pb-style=XW9HH6L]{text-align:center;margin-top:50px}#html-body [data-pb-style=SALJBUC]{display:inline-block}#html-body [data-pb-style=OTI5KAR]{text-align:center;margin-bottom:0;padding-left:36px;padding-right:36px}#html-body [data-pb-style=OML03XO]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=BFBFW6D]{text-align:right;border-style:none}#html-body [data-pb-style=PJP129T],#html-body [data-pb-style=Q91YCG3]{max-width:100%;height:auto}#html-body [data-pb-style=MTJGW0O],#html-body [data-pb-style=NBJ8A9A]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=NBJ8A9A]{justify-content:flex-start;display:flex;flex-direction:column;margin:0;padding:0 0 100px}#html-body [data-pb-style=MTJGW0O]{align-self:stretch}#html-body [data-pb-style=K8SB15Y]{display:flex;width:100%}#html-body [data-pb-style=TDDEFK1]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;text-align:left;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=LU0ODLW]{text-align:right;border-style:none}#html-body [data-pb-style=LC9JOV4],#html-body [data-pb-style=N2QX0HO]{max-width:100%;height:auto}#html-body [data-pb-style=G8D8PH4]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;text-align:left;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=ACKRNXK],#html-body [data-pb-style=N65WBEB],#html-body [data-pb-style=YDY5HLM]{text-align:center}#html-body [data-pb-style=HXIYHK3]{text-align:center;margin-top:50px}#html-body [data-pb-style=SBPMK84]{text-align:center;display:none;margin-top:50px}#html-body [data-pb-style=FACP4QE]{display:inline-block}#html-body [data-pb-style=GU14P4Y]{text-align:center;margin-bottom:0;padding-left:36px;padding-right:36px}#html-body [data-pb-style=ISWYAMI]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}@media only screen and (max-width: 768px) { #html-body [data-pb-style=BFBFW6D],#html-body [data-pb-style=LU0ODLW]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="lounge-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="MLYHOSJ"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="VLC0WVS"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="RQ7IA4U"><div class="pagebuilder-column lounge-text" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="FEKLQLS"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="R3KKCQV">完美新生三效賦活精華</h2><h4 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="G9QQ93F">三管精華 三管精準抗老</h4><div class="block-content-recom" data-content-type="text" data-appearance="default" data-element="main"><p style="text-align: center;"><span style="font-size: 18px;">3種精華集成一瓶，快速、準確、強效</span></p></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="KS0YCW1"><div class="product_tags block-content-recom">精準狙擊肌膚老化困擾，有效提升肌膚光彩、
肌底彈性、撫平皺紋</div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="XW9HH6L"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="SALJBUC"><a class="pagebuilder-button-secondary" href="concentrated-ginseng-renewing-serum-ex.html" target="" data-link-type="default" data-element="link" data-pb-style="OTI5KAR"><span data-element="link_text">了解更多</span></a></div></div></div><div class="pagebuilder-column image-odd" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="OML03XO"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="BFBFW6D"><a href="/experience/archive/etc_concentrated_ginseng_renewing_serum_2022" target="" data-link-type="default" title="皇牌人參安瓶" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_D_990px__1.jpg}}" alt="皇牌人參安瓶" title="皇牌人參安瓶" data-element="desktop_image" data-pb-style="PJP129T"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_D_990px__2.jpg}}" alt="皇牌人參安瓶" title="皇牌人參安瓶" data-element="mobile_image" data-pb-style="Q91YCG3"></a></figure></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div class="lounge-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="NBJ8A9A"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="MTJGW0O"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="K8SB15Y"><div class="pagebuilder-column image-odd" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="TDDEFK1"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="LU0ODLW"><a href="/brand-belief.html" target="" data-link-type="default" title=" skin recovery" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_E_990px__1.jpg}}" alt=" skin recovery" title=" skin recovery" data-element="desktop_image" data-pb-style="N2QX0HO"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_E_990px__2.jpg}}" alt=" skin recovery" title=" skin recovery" data-element="mobile_image" data-pb-style="LC9JOV4"></a></figure></div><div class="pagebuilder-column lounge-text" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="G8D8PH4"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="N65WBEB">品牌故事</h2><h4 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="ACKRNXK">我耀我的光</h4><div class="block-content-recom" data-content-type="text" data-appearance="default" data-element="main"><p style="text-align: center;"><span style="font-size: 18px;">from skin to my life. 「LANEIGE」，法文中的「雪」， 意指美的永恆定義－如雪般飽水淨透的無瑕肌膚。</span></p></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="YDY5HLM"><div class="product_tags block-content-recom">自1994年創立，28年來蘭芝精研肌膚科學，
時間的腳步從未停止，肌膚的狀態也細微的不停地改變著。
FEEL the GLOW  with LANEIGE.</div></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="HXIYHK3"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="OMYOK0T"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="QLODO44"><a class="pagebuilder-button-secondary" href="/brand-story" target="" data-link-type="default" data-element="link" data-pb-style="S72461O"><span data-element="link_text">了解更多</span></a></div></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="SBPMK84"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="FACP4QE"><a class="pagebuilder-button-secondary" href="brand-belief.html" target="" data-link-type="default" data-element="link" data-pb-style="GU14P4Y"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="ISWYAMI"><div data-content-type="html" data-appearance="default" data-element="main"><style>
.lounge-text h2 {font-size: 48px;position: relative;margin-bottom: 68px;}
.lounge-text .banner-text-width {
  max-width: 300px;
  margin-left: auto;
  margin-right: auto;
}
.lounge-text h2::after {
  display: block;
  content: \'\';
  position: absolute;
  z-index: 0;
  width: 1px;
  height: 40px;
  background-color: #222;
  left: 50%;
  bottom: -10px;
  transform: translateY(100%);
}
.lounge-text h4 {
  margin-bottom: 5px;
  font-weight: 700;
  font-size: 24px;
  color: #222;
}
.lounge-text p {
  line-height: 32px;
  color: #767676;
}
.lounge-text .product_tags {
  margin: 20px 0 0 0;
}
.lounge-text .pagebuilder-button-secondary {background: transparent !important;}
.lounge-text .pagebuilder-button-secondary:hover,.lounge-text .pagebuilder-button-secondary:active {background: #333 !important}


@media (min-width: 768px) {
.lounge-text h1 { line-height: 55px;}
.lounge-text p {line-height: 32px;}
.lounge-row .image-odd img {margin-left: auto !important;}
}

@media (max-width: 767px) {
.block-content-recom {
    padding-left: 0px !important;
    padding-right: 0px !important;
}
.lounge-row {padding: 42px 0 0 !important;margin: 0 20px 70px !important; position: relative; background-color: transparent;}
.lounge-text h2::after {content: "";background: 0;}
.lounge-row .image-odd {order: -1}
.lounge-text h2 {position: absolute; right: 0; left: 0; top: 0;font-size: 21px;line-height: 26px;text-align: left !important;margin-left: 0;}
.lounge-text hr {position: absolute; right: 0; left: 30px; top: 60px; width: auto !important;}
.lounge-text {padding: 0 !important; text-align: left !important;}
.image-odd img {width: 100%;}
.lounge-text h4 {text-align: left !important;margin: 12px 0 0;font-size: 18px;line-height: 22px;}
.lounge-text .banner-text-width {text-align: left !important;margin: 12px 0 0;font-size: 18px;line-height: 22px;max-width: 100%;}
.lounge-text p {text-align: left !important;margin: 8px 0 0 !important;line-height: 20px;}
.lounge-text [data-content-type=\'text\'] p span {font-size: 14px !important;color: #222;line-height: 20px;}
.lounge-text .product_tags {margin: 12px 0 0 0;text-align: left;font-size: 14px;line-height: 18px;}
.lounge-text [data-content-type=\'buttons\'] {margin: 24px 0 0 !important;text-align: center !important;}
.lounge-text .pagebuilder-button-secondary {font-size: 14px;padding: 0 21px !important;min-width: 240px;}
}

@media (max-width: 480px) {
.lounge-text p span {font-size: 15px !important;}
.lounge-text .pagebuilder-button-secondary {min-width: 120px;}
}
.block-content-recom{
padding-left: 60px;
padding-right: 60px;
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