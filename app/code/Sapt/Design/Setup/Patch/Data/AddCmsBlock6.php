<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock6 implements DataPatchInterface
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
            'title' => 'Tw Block Menu Mobile',
            'identifier' => 'tw_block_menu_mobile',
            'content' => '<style>#html-body [data-pb-style=KRFQ550],#html-body [data-pb-style=XLW3ED3]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=KRFQ550]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=XLW3ED3]{align-self:stretch}#html-body [data-pb-style=VU2AVXN]{display:flex;width:100%}#html-body [data-pb-style=YO37UJB]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=O9LSW02]{border-style:none}#html-body [data-pb-style=HB6K0U0],#html-body [data-pb-style=RYIIRBA]{max-width:100%;height:auto}#html-body [data-pb-style=IVEUB46]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=M0CKY2S],#html-body [data-pb-style=MYO6TYE]{display:none}#html-body [data-pb-style=B5CJ2DW]{display:inline-block}#html-body [data-pb-style=AYSH1RR]{text-align:center}#html-body [data-pb-style=KQ5TPAQ],#html-body [data-pb-style=OSH1C7S]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=KQ5TPAQ]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=OSH1C7S]{align-self:stretch}#html-body [data-pb-style=X7NL5C5]{display:flex;width:100%}#html-body [data-pb-style=M5JIHI3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=W04QH7U]{border-style:none}#html-body [data-pb-style=GBCV56C],#html-body [data-pb-style=QQ4SF0A]{max-width:100%;height:auto}#html-body [data-pb-style=AS3EG1Q]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=LVDRCFV],#html-body [data-pb-style=QFV114J]{display:none}#html-body [data-pb-style=EIS7TDF]{display:inline-block}#html-body [data-pb-style=EE6K4KI]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=O9LSW02],#html-body [data-pb-style=W04QH7U]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="blog-menu-mobile" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="KRFQ550"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="XLW3ED3"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="VU2AVXN"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="YO37UJB"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="O9LSW02"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D004_A_1.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="HB6K0U0"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D004_A_1.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="RYIIRBA"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="IVEUB46"><h4 data-content-type="heading" data-appearance="default" data-element="main">完美新生三效賦活精華</h4><div data-content-type="text" data-appearance="default" data-element="main" data-pb-style="MYO6TYE"><p id="HOE2D9W">3種精華集成一瓶，快速、準確、強效</p></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="M0CKY2S"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="B5CJ2DW"><div class="pagebuilder-button-primary" data-element="empty_link" data-pb-style="AYSH1RR"><span data-element="link_text">了解更多</span></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-viewmore" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="TXW3EEA"><a class="pagebuilder-button-secondary" href="water-bank-blue-hyaluronic-cream-50ml-for-dry-skin.html" target="" data-link-type="default" data-element="link" data-pb-style="E5UVN1P"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div class="blog-menu-mobile" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="KQ5TPAQ"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="OSH1C7S"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="X7NL5C5"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="M5JIHI3"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="W04QH7U"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D004_B_1.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="GBCV56C"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D004_B_1.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="QQ4SF0A"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="AS3EG1Q"><h4 data-content-type="heading" data-appearance="default" data-element="main">水酷修護保濕精華</h4><div data-content-type="text" data-appearance="default" data-element="main" data-pb-style="QFV114J"><p id="BMDT223">立即提升肌膚六倍保水力！速效補水，一整天水潤有感。</p></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="LVDRCFV"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="EIS7TDF"><div class="pagebuilder-button-primary" data-element="empty_link" data-pb-style="EE6K4KI"><span data-element="link_text">了解更多</span></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-viewmore" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="TXW3EEA"><a class="pagebuilder-button-secondary" href="water-bank-blue-hyaluronic-cream-50ml-for-dry-skin.html" target="" data-link-type="default" data-element="link" data-pb-style="E5UVN1P"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
.satp-megamenu.navigation > ul > li.ui-menu-item>.open-children-toggle {
    top: 14px;
}
.blog-menu-mobile .pagebuilder-column{
flex-basis: auto !important;
}
.blog-menu-mobile h4{
font-size: 16px;
    font-family: Theinhardt;
    font-weight: 600;
    margin-top: 15px;
}
.blog-menu-mobile .pagebuilder-button-secondary{
    border: 0;
    padding: 0;
    font-size: 14px;
    text-transform: capitalize;
    font-family: Theinhardt;
    background: none;
    font-weight: 700;
    color: #666;
margin-top: 15px;
}
.blog-menu-mobile p{
margin-top: 15px;
margin-bottom: 15px;
}

.blog-menu-mobile .btn-viewmore::after {
    content: "\e80b";
    font-family: \'fontello\';
    font-size: 12px;
    margin-left: 0px;
    font-weight: 700;
    position: relative;
    top: 3px;
}
.blog-menu-mobile .btn-viewmore a{
font-size: 14px !important;
padding-left: 0px !important;
}
.blog-menu-mobile .btn-viewmore a:hover{
                border: 0px solid #333333 !important;
}
</style></div>',
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