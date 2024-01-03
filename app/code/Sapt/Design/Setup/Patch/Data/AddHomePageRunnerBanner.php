<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
class AddHomePageRunnerBanner implements DataPatchInterface
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
            'title' => 'TW Laneige  Top Banner - Runner',
            'identifier' => 'tw_top_banner_runner',
            'content' => '<style>#html-body [data-pb-style=K63LNY7]{border-style:none;border-width:1px;border-radius:0;margin:0;padding:10px}#html-body [data-pb-style=K63LNY7],#html-body [data-pb-style=OAO462T]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=RO6KCI3]{border-style:none;border-width:1px;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=W5N3JJ7]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=PWM9CJC]{display:flex;width:100%}#html-body [data-pb-style=C3S5NWM]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=GNQAN0M]{text-align:center}#html-body [data-pb-style=SO4EX3R]{text-align:center;border-style:none;border-width:1px;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=U2J4LVG]{margin:0}#html-body [data-pb-style=E54KY10]{background-position:left top;background-size:cover;background-repeat:no-repeat;border-style:none;border-width:1px;border-radius:0}#html-body [data-pb-style=BPVENT1]{padding:0;background-color:transparent}#html-body [data-pb-style=FBJANDN]{margin:0}#html-body [data-pb-style=VJB56PU]{background-position:left top;background-size:cover;background-repeat:no-repeat;border-style:none;border-width:1px;border-radius:0}#html-body [data-pb-style=X6O744F]{padding:0;background-color:transparent}</style><div class="laneige-top-banner hideslide hideslide-display-none" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="K63LNY7"><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="RO6KCI3"><style>
.page-header {
  position: relative;
}
.hideslide-display-none {
  display: none !important;
}
body[class*="popup"] .laneige-top-banner {display: none !important;}
.laneige-top-banner {background: #C5DEFF; color: #222; padding: 12px !important;}
.search-open .block-search .label.active + .control {top: 100px;}
.search-open .block-search .label.active:after {top: 100px;}
@media only screen and (max-width: 767px) {
.socials.links {padding-bottom: 100px;}
body.filter-active .laneige-top-banner {display: none !important;}
}
.laneige-top-banner .pagebuilder-slide-wrapper [data-element=\'content\']{
    min-height: 0;font-size: 16px; line-height: 24px;
}
.btn-close-top {
  position: absolute;
  right: 0;
  top: 0px;
  background: none;
  padding: 0;
}
.btn-close-top::before {
  content: "\e616";
  font-family: \'icons\';
  font-size: 14px;
}
.btn-close-top span {
  display: none;
}
.btn-close-top:hover {
  border: 0;
  color: #222;
  background: none;
}
.modal-popup .laneige-top-banner {
  display: none !important;
}
.content_top_banner {
    max-width: 1380px;
    margin: 0 auto;
    padding: 0 20px;
    position: relative;
}
@media (max-width: 1420px) {
.content_top_banner {
    max-width: 100%;
}
.btn-close-top {
    right: 20px;
}
}
@media (max-width: 767px) {
.btn-close-top {
    right: 5px;
}
.content_top_banner span {
  font-size: 12px !important;
}
.laneige-top-banner .pagebuilder-slide-wrapper [data-element="content"] {
  font-size: 12px;
}
}
</style></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="W5N3JJ7"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="PWM9CJC"><div class="pagebuilder-column content_top_banner" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="C3S5NWM"><div class="pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="true" data-autoplay-speed="6000" data-fade="true" data-infinite-loop="true" data-show-arrows="false" data-show-dots="false" data-element="main" data-pb-style="SO4EX3R"><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main" data-pb-style="U2J4LVG"><a href="https://tw.laneige.com/best-new/vbom.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="E54KY10"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="" title="" data-element="overlay" data-pb-style="BPVENT1"><div class="pagebuilder-poster-content"><div data-element="content"><p data-syno-attrs="{"textMarks":[{"_":"font_size","value":"18pt"},{"_":"font_family","value":"Calibri"},{"_":"color","value":"#000000"}]}">全館結帳滿$1000免運費</p></div></div></div></div></a></div><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main" data-pb-style="FBJANDN"><a href="https://tw.laneige.com/events/detail/index/id/109" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="VJB56PU"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="" title="" data-element="overlay" data-pb-style="X6O744F"><div class="pagebuilder-poster-content"><div data-element="content"><p data-syno-attrs="{"textMarks":[{"_":"font_size","value":"18pt"},{"_":"font_family","value":"Calibri"},{"_":"color","value":"#000000"}]}">結帳用LINE Pay綁定中信LINE Pay卡享6%回饋</p></div></div></div></div></a></div></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="GNQAN0M"><button class="btn-close-top hideslide" id=\'satpHideShowjs\' type="button"><span>Close</span></button></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><script>
require([
    \'jquery\'
], function ($) {
    $(document).ready(function () {
        console.log("has context menu");
        $(\'.laneige-top-banner.hideslide\').removeClass(\'hideslide-display-none\');

        $(\'.laneige-top-banner #satpHideShowjs\').on( "click", function() {
            $(\'.laneige-top-banner.hideslide\').each(function() {
                $(this).hide();
            });
        });
    });
});
</script></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="OAO462T"><div data-content-type="html" data-appearance="default" data-element="main"><style>
#ui-id-3 {color:#fa860b !important;}
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