<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;
class AddHomePageBanner implements DataPatchInterface
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
            'title' => 'TW Laneige Main Hero Banner Slider',
            'identifier' => 'tw_laneige_main_hero_banner_slider',
            'content' => '<style>#html-body [data-pb-style=OCTMG1X]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin-bottom:80px}#html-body [data-pb-style=SRJQLIK]{margin:0;padding:0}#html-body [data-pb-style=H77EJOL],#html-body [data-pb-style=WUVLOI9]{border-style:none;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=WUVLOI9]{text-align:left;min-height:540px;margin:10px 0 0}#html-body [data-pb-style=W2FIAMI]{margin:0}#html-body [data-pb-style=MRLJQO8]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=QC1L7OC]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=U10R720]{opacity:1;visibility:visible}#html-body [data-pb-style=OMVLEWM]{margin:0}#html-body [data-pb-style=KBEA0J7]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=XC2DQ7J]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=P2T63J1]{opacity:1;visibility:visible}#html-body [data-pb-style=T7O7EYH]{margin:0}#html-body [data-pb-style=UBE94M1]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=VMWDUA7]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=XVDWPNE]{opacity:1;visibility:visible}#html-body [data-pb-style=JMOKWLW]{border-radius:0;margin:0;padding:0}</style><div class="main-hero-slider" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="OCTMG1X"><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="SRJQLIK"><script type="text/javascript" xml="space">
    require([\'jquery\', \'matchMedia\', \'domReady!\'], function($, mediaCheck) {

        // Add AP Tag to HeroBanner
        $(window).on(\'load\', function()  {
            var heroSlide = $(\'.hero-pagebuilder-slider .slick-slide\');

            heroSlide.each(function(index, item) {
                var slideName = \'\',
                    hasLink = $(this).find(\'a\');

                if (hasLink.length > 0) {
                    slideName = $(this).find(\'div[data-content-type="slide"]\').attr(\'data-slide-name\');

                    hasLink.attr(\'ap-click-area\', \'MAIN\');
                    hasLink.attr(\'ap-click-name\', \'HeroBanner\');
                    hasLink.attr(\'ap-click-data\', slideName);
                }
            })

        });
    });
</script></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="H77EJOL"><script type="text/javascript" xml="space">
require([\'jquery\',\'slick\'],function($,slick){
  $(document).ready(function() {

            $(".hero-pagebuilder-slider").on(\'init\', function(event, slick){

$(\'<div class="wrap-progress kv-progress">\n\' +
                \'      <div class="inner-progress">\n\' +
                \'        <div class="progress"><span class="progress_ing"></span></div>\n\' +
                \'      </div>\n\' +
                \'    </div>\').appendTo(\'.hero-pagebuilder-slider .slick-button\');
            $KvProgressBarWrap = $(\'.wrap-progress.kv-progress\');
            $KvProgressBar = $(\'.kv-progress .progress .progress_ing\');

                var slideNum = slick.$slides.length;

                if (slideNum > 1) $KvProgressBarWrap.css(\'opacity\', 1);
        
                var calc = (100 / (slick.slideCount / 1));

                $KvProgressBar.css({
                    \'width\': calc + \'%\',
                });

                if (slick.$slides[0].classList.contains(\'txt_black\')) $KvProgressBarWrap.addClass(\'txt_black\');
                else $KvProgressBarWrap.removeClass(\'txt_black\');
            });
            $(".hero-pagebuilder-slider").on("beforeChange", function (event, slick, currentSlide, nextSlide){
                if(typeof $KvProgressBarWrap != \'undefined\') {
                    if (slick.$slides[nextSlide].classList.contains(\'txt_black\')) $KvProgressBarWrap.addClass(\'txt_black\');
                    else $KvProgressBarWrap.removeClass(\'txt_black\');
                }

                var calc = (100 / (slick.slideCount / 1)) * (nextSlide + 1);


                if (typeof $KvProgressBar != \'undefined\') {
                    $KvProgressBar.css({
                        \'width\': calc + \'%\',
                    });
                }
            });
    if(isApplication()) {
      $(".hero-pagebuilder-slider").on("afterChange", function (event, slick, currentSlide){
        $(".hero-pagebuilder-slider .slick-active .pagebuilder-poster-content").animate({opacity: \'1\'}, 1500);
      })

      $(".hero-pagebuilder-slider").on("beforeChange", function (event, slick, currentSlide){
        $(".hero-pagebuilder-slider .pagebuilder-overlay").css(\'opacity\',\'0\');
        $(".hero-pagebuilder-slider .pagebuilder-poster-content").css(\'opacity\',\'0\');
      })
    }else {
      $(".hero-pagebuilder-slider").on("afterChange", function (event, slick, currentSlide){
        if($(window).width() >= 768){
          $(".hero-pagebuilder-slider .slick-active .pagebuilder-overlay").animate({width: "43.5%", opacity: \'1\'}, 10 , function() {
            if($(".hero-pagebuilder-slider .slick-active .pagebuilder-overlay").css(\'opacity\') == 1) {
              $(".hero-pagebuilder-slider .slick-active .pagebuilder-poster-content").animate({opacity: \'1\'}, 1500);
            }
          });
        }else {
          $(".hero-pagebuilder-slider .slick-active .pagebuilder-poster-content").animate({opacity: \'1\'}, 1500);
        }
      })

      $(".hero-pagebuilder-slider").on("beforeChange", function (event, slick, currentSlide){
        if($(window).width() >= 768){
          $(".hero-pagebuilder-slider .pagebuilder-overlay").css(\'width\',\'0\');
        }

        $(".hero-pagebuilder-slider .pagebuilder-overlay").css(\'opacity\',\'0\');
        $(".hero-pagebuilder-slider .pagebuilder-poster-content").css(\'opacity\',\'0\');
      })
    }
  });

  function isApplication()
    {
        var isMobile = false;
        if(navigator.userAgent.match(/Android|Mobile|iP(hone|od|ad)|BlackBerry|IEMobile|Kindle|NetFront|Silk-Accelerated|(hpw|web)OS|Fennec|Minimo|Opera M(obi|ini)|Blazer|Dolfin|Dolphin|Skyfire|Zune/) || $(window).width() < 768){
            if($(window).width() < 768){
                isMobile = true;
            }
        }
        return isMobile;
    }
});
</script>
<style>
.hero-pagebuilder-slider .slick-arrow {z-index: 1 !important;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h1, 
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p, 
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] button {color: #fff}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element="content"] h1 {font-size: 48px;margin-bottom: 5px;font-weight: 500;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element="content"] h2 {margin-bottom: 5px;}
.hero-pagebuilder-slider .pagebuilder-button-link {text-transform: uppercase; font-size: 15px; width: 160px; margin-top: 50px; text-align: center;}

@media (max-width: 767px) {
.pagebuilder-slider.hero-pagebuilder-slider {margin-top: 0 !important;}
.main-hero-slider .wrap-progress .progress {background-color: #dddddd;}
.main-hero-slider .wrap-progress .progress_ing {background-color: #222222;}
    .main-hero-slider {padding-bottom: 280px !important;margin-bottom: 0 !important;overflow: hidden;}
.hero-pagebuilder-slider .slick-button {bottom: -35%;left: 0;right: 0;padding: 0;max-width: 320px;margin: 0 auto 0;}
    .hero-pagebuilder-slider .slick-button li button {background: #eee;}
    .hero-pagebuilder-slider .slick-pause::after {width: 20px;font-size: 10px;line-height: 20px;content: "\e812";font-family: \'fontello\';color: #222 !important;}
.hero-pagebuilder-slider .slick-pause.active::after {font-size: 10px;line-height: 20px;content: "\e813";font-family: \'fontello\';}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper {text-align: center !important; position: relative;background-position: 15% center !important;}
    .hero-pagebuilder-slider .pagebuilder-slide-button {display: none;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element="content"] h1 {margin-bottom: 0;line-height: 26px;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element="content"] h2 {margin-bottom: 0;line-height: 26px;text-align: center !important;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h1 span {font-size: 24px !important;margin-bottom: 0;line-height: 26px !important;color: #222222 !important;font-weight: 700;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h2 span {font-size: 24px !important;margin-bottom: 0;line-height: 26px !important;color: #222222 !important;font-weight: 700;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p {margin-top: 12px;}
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p span {font-size: 14px !important;color: #222 !important;line-height: 20px !important;}
    .hero-pagebuilder-slider .pagebuilder-overlay.pagebuilder-poster-overlay {min-height: auto !important;position: absolute;bottom: -145px;width: 100% !important;opacity: 1 !important;z-index: 9;}
    .hero-pagebuilder-slider .pagebuilder-poster-content {padding: 20px 20px;}
    .hero-pagebuilder-slider div {overflow: initial !important;}
}
@media (max-width: 640px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {background-position: center !important;}
}
@media (max-width: 480px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {min-height: 414px !important;}
.main-hero-slider {padding-bottom: 85px !important;}
.hero-pagebuilder-slider .slick-button {bottom: -25px;max-width: 140px;}
.main-hero-slider .wrap-progress .progress {width: 110px;}
}
@media (max-width: 375px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {min-height: 270px !important;}
.main-hero-slider {padding-bottom: 56px !important;}
.hero-pagebuilder-slider .slick-button {bottom: 0;}
.hero-pagebuilder-slider .pagebuilder-overlay.pagebuilder-poster-overlay {bottom: -138px;}
.hero-pagebuilder-slider .pagebuilder-poster-content {padding: 16px 20px;}
.pagebuilder-slider.hero-pagebuilder-slider {min-height: 420px !important;}
}
@media (min-width: 768px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {position: relative;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h1 span {font-size: 34px !important;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h2 span {font-size: 34px !important;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p {font-size: 16px; margin: 10px 0 0 0;}
.hero-pagebuilder-slider .pagebuilder-poster-overlay {width: 43.5%; margin: 0 0 0 auto; position: absolute; right: 0; bottom: 0; top: 0;}
.hero-pagebuilder-slider .slick-button li button {background: #fff;}
.hero-pagebuilder-slider .slick-pause:after {color: #fff;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper .pagebuilder-poster-content {padding: 0 50px 0 30px; box-sizing: border-box; margin-top: -50px;}
.hero-pagebuilder-slider .pagebuilder-button-link {margin-top: 30px;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {background-position: 30% 0 !important;}
}
@media (min-width: 1024px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h1 span {font-size: 40px !important;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] h2 span {font-size: 40px !important;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p {font-size: 18px; margin: 30px 0 0 0;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper .pagebuilder-poster-content {padding: 0 25% 0 30px; box-sizing: border-box; margin-top: -50px;}
}
@media (min-width: 1280px) {
.hero-pagebuilder-slider .pagebuilder-slide-wrapper .pagebuilder-poster-content {padding: 0 38% 0 30px; box-sizing: border-box; margin-top: -50px;}
.hero-pagebuilder-slider .pagebuilder-slide-wrapper {background-position: center !important;}
}
</style></div><div class="pagebuilder-slider hero-pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="true" data-autoplay-speed="4000" data-fade="true" data-infinite-loop="true" data-show-arrows="true" data-show-dots="true" data-element="main" data-pb-style="WUVLOI9"><div class="btn_white header_center" data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="W2FIAMI"><a href="https://tw.laneige.com/skincare/line/perfect-renew.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/20231213-_BN_PC_2880x810_.jpg}}\",\"mobile_image\":\"{{media url=wysiwyg/20231213-_BN_MO_720x540_.jpg}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="MRLJQO8"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="網店獨家迎新禮遇" title="" data-element="overlay" data-pb-style="QC1L7OC"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>完美新生三效賦活精華 #三管精華</strong></h2><div></div><div id="Y76FESP" class="ewa-rteLine"><span style="font-size: 16px;">3種精華集成一瓶，快速、準確、強效，</span></div><div class="ewa-rteLine"><span style="font-size: 16px;">精準狙擊肌膚老化困擾，有效提升肌膚光彩、肌底彈性、撫平皺紋。</span></div></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="U10R720">了解詳情</button></div></div></div></a></div><div class="btn_white header_center" data-content-type="slide" data-slide-name="氣墊王者！NEO型塑霧感/光感氣墊EX" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="OMVLEWM"><a href="https://tw.laneige.com/make-up/face/cushion.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/cushion_PC_2880_810.jpg}}\",\"mobile_image\":\"{{media url=wysiwyg/cushion_MO_720_540_1.jpg}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="KBEA0J7"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="網店獨家迎新禮遇" title="" data-element="overlay" data-pb-style="XC2DQ7J"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>氣墊王者！NEO型塑霧感/光感氣墊EX</strong></h2><div></div><div id="Y76FESP" class="ewa-rteLine"><div id="Y76FESP" class="ewa-rteLine"><span style="font-size: 16px;">蘭芝首創3大超革新技術！NEO無痕貼膚科技，50小時保濕透亮，更輕盈服貼不沾染。獨家FAM緊緻透亮科技，一拍如薄荷般清爽的微霧光NEO妝容</span><span style="font-size: 16px;">。</span></div></div></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="P2T63J1">了解詳情</button></div></div></div></a></div><div class="btn_white h3_header mobile_slide_center150 slide_bottom" data-content-type="slide" data-slide-name="Merry Sweet Holiday！" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="T7O7EYH"><a href="https://tw.laneige.com/best-new/vbom.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/holiday_pc_2880x810_1.png}}\",\"mobile_image\":\"{{media url=wysiwyg/holiday_mo_720x540_1.png}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="UBE94M1"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="會員禮遇" title="" data-element="overlay" data-pb-style="VMWDUA7"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>Merry Sweet Holiday！</strong></h2><div></div><div><span style="font-size: 16px;">蘭芝首創3大超革新技術！NEO無痕貼膚科技，50小時保濕透亮，更輕盈服貼不沾染。獨家FAM緊緻透亮科技，一拍如薄荷般清爽的微霧光NEO妝容。</span></div><h2 style="line-height: 40px;"><span style="color: #000000; font-size: 18px; font-family: \'Noto Sans\', sans-serif; background-color: transparent;">​</span></h2></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="XVDWPNE">馬上逛逛</button></div></div></div></a></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
    .hero-pagebuilder-slider .slick-slide .btn_black .pagebuilder-slide-wrapper button.pagebuilder-slide-button {
      border:1px solid #000000 !important;
      background: transparent;
      color:#000000 !important;
border-radius: 30px;
    }
    .hero-pagebuilder-slider .slick-slide .btn_white .pagebuilder-slide-wrapper button.pagebuilder-slide-button {
      border:1px solid #000000 !important;
      background-color: transparent;
      color:#000000 !important;
border-radius: 30px;
    }
    .hero-pagebuilder-slider .slick-slide .header_white .pagebuilder-slide-wrapper h2 span{
      color: white !important;
    }
    @media (max-width: 768px){
      .hero-pagebuilder-slider .slick-slide .header_white .pagebuilder-slide-wrapper h2 span{
        color: black !important;
      }
    }
    @media (max-width: 768px){
      .hero-pagebuilder-slider .slick-slide .header_center .pagebuilder-slide-wrapper h2{
        margin-left: auto;
        margin-right: auto;
      }
    }
    .hero-pagebuilder-slider .slick-slide .h3_header .pagebuilder-slide-wrapper h3 span {
      display: block;
    }

    @media (max-width: 740px){
      .hero-pagebuilder-slider .slick-slide .h3_header .pagebuilder-slide-wrapper h3 span {
        display: none;
        font-size: 24px !important;
        margin-bottom: 0;
        //line-height: 26px !important;
        color: #222222 !important;
        font-weight: 700;
      }
    }

    @media (min-width: 1024px){
      .hero-pagebuilder-slider .pagebuilder-slide-wrapper h3 span {
        font-size: 40px !important;
      }
      .hero-pagebuilder-slider .p_margin-0 .pagebuilder-slide-wrapper  p
          margin:0;
       }

    }

    @media (min-width: 768px){
      .hero-pagebuilder-slider .pagebuilder-slide-wrapper h3 span {
        font-size: 34px !important;
      }
    }
    @media (max-width: 768px){
      .hero-pagebuilder-slider .slick-slide .pagebuilder-overlay.pagebuilder-poster-overlay{
           bottom: -140px;
      }
      .hero-pagebuilder-slider .slick-slide .header_center .pagebuilder-overlay.pagebuilder-poster-overlay{
           bottom: -125px;
      }
    }
    @media (max-width: 480px){
      .hero-pagebuilder-slider .slick-slide .pagebuilder-overlay.pagebuilder-poster-overlay{
           bottom: -175px;
      }
      .hero-pagebuilder-slider .slick-button {
         bottom: -60px;
      }
       .hero-pagebuilder-slider .slick-slide .tnc_bottom_3 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -180px;
       }
    }
    @media (max-width: 375px){
      .hero-pagebuilder-slider .pagebuilder-overlay.pagebuilder-poster-overlay{
         bottom: -175px !important;
      }
      .hero-pagebuilder-slider .slick-slide .header_center .pagebuilder-overlay.pagebuilder-poster-overlay{
           bottom: -135px !important;
      }
    }
.hero-pagebuilder-slider .header_white .pagebuilder-slide-wrapper [data-element=\'content\'] h2 span{
    color: white;
}
.hero-pagebuilder-slider .text_desktop_d-none .pagebuilder-slide-wrapper .pagebuilder-poster-content{
   display:none;
}

@media (min-width: 769px){
 .hero-pagebuilder-slider .desktop_text_left .pagebuilder-poster-content{
    margin-left: -200%;
    margin-right: 0;
    padding-right: 25%;
    margin-top:0;
 }
 .hero-pagebuilder-slider .header_0208 .pagebuilder-slide-wrapper [data-element=\'content\'] h6 span{
    display: none;
 }
}

@media (max-width: 1200px)and (min-width: 769px){
 .hero-pagebuilder-slider .desktop_text_left .pagebuilder-poster-content{
    padding: 0 22% 0 0;
 }
}

@media (max-width: 768px){
.hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] p{
margin-top:10px;
}
 .hero-pagebuilder-slider .header_0208 .pagebuilder-slide-wrapper [data-element=\'content\'] h2 span{
    display: none;
 }
 .hero-pagebuilder-slider .header_0208 .pagebuilder-slide-wrapper [data-element=\'content\'] h6 span{
    font-size: 24px !important;
    margin-bottom: 0;
    line-height: 26px !important;
    color: #222222 !important;
    font-weight: 700;
 }
 .hero-pagebuilder-slider .slick-slide .header_0208 .pagebuilder-overlay.pagebuilder-poster-overlay{
   bottom: -182px;
 }
.hero-pagebuilder-slider .text_desktop_d-none .pagebuilder-slide-wrapper .pagebuilder-poster-content{
   display: block;
 }
}
    @media (max-width: 768px){
      .hero-pagebuilder-slider .slick-slide .tnc_bottom .pagebuilder-slide-wrapper div span{
        color: black !important;
      }
    }
    @media (max-width: 768px){
       .hero-pagebuilder-slider .pagebuilder-overlay.pagebuilder-poster-overlay{
         z-index: 99;
       }
       .hero-pagebuilder-slider .slick-slide .mobile_slide_center140 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -140px;
       }
       .hero-pagebuilder-slider .slick-slide .mobile_slide_center150 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -150px;
       }
       .hero-pagebuilder-slider .slick-slide .mobile_slide_center160 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -160px;
       }
       .hero-pagebuilder-slider .slick-slide .slide_bottom .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -170px;
       }
       .hero-pagebuilder-slider .slick-slide .slide_bottom_2 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -193px;
       }
       .hero-pagebuilder-slider .slick-slide .tnc_bottom_3 .pagebuilder-overlay.pagebuilder-poster-overlay{
          bottom: -180px;
       }
       .hero-pagebuilder-slider .pagebuilder-slide-wrapper [data-element=\'content\'] div span{
             font-size: 14px !important;
             color: black !important;
       }
       .hero-pagebuilder-slider .slick-slide .pagebuilder-overlay.pagebuilder-poster-overlay{
           padding:0;
       }
    }
.hero-pagebuilder-slider .header_2606 .pagebuilder-slide-wrapper [data-element=\'content\'] p{
  margin: 20px 0 0 0
}
 @media (max-width: 768px){
   .hero-pagebuilder-slider .header_2606 .pagebuilder-slide-wrapper [data-element=\'content\'] p{
     margin: 20px 0 0 0
  }
      .hero-pagebuilder-slider .pagebuilder-poster-content{
           padding: 0px 10px; z-index:999!important;
      }
}
    @media (min-width: 1024px){
       .hero-pagebuilder-slider .header_2606 .pagebuilder-slide-wrapper .pagebuilder-poster-content p{
          margin: 20px 0 0 0;
       }
       .hero-pagebuilder-slider .header_2606 .pagebuilder-slide-wrapper .pagebuilder-poster-content{
          padding: 0 25% 0 30px;
          margin-left: 10%;
       }
    }

@media (min-width: 1280px) {
    .hero-pagebuilder-slider .pagebuilder-slide-wrapper .pagebuilder-poster-content {
        padding: 0 25% 0 30px;
    }
}

@media (min-width: 768px) {
    .hero-pagebuilder-slider .slick-pause:after {
        color: #000;
    }
}

@media screen and (min-width: 1px) {
    .main-hero-slider .wrap-progress .progress_ing {
        background-color: #000;
    }
}

</style></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="JMOKWLW"><script type="text/javascript" xml="space">
    require([\'jquery\', \'slick\', \'matchMedia\', \'domReady!\'], function($, slick, mediaCheck) {

        // Add AP Tag to HeroBanner
        $(window).on(\'load\', function(){
            var heroSlide = $(\'.hero-pagebuilder-slider .slick-slide\');

            heroSlide.each(function(index, item) {
                var slideName = \'\',
                    hasLink = $(this).find(\'a\');

                if (hasLink.length > 0) {
                    slideName = $(this).find(\'div[data-content-type="slide"]\').attr(\'data-slide-name\');

                    hasLink.attr(\'ap-click-area\', \'MAIN\');
                    hasLink.attr(\'ap-click-name\', \'HeroBanner\');
                    hasLink.attr(\'ap-click-data\', slideName);
                }
            });

        });

		var isChanging = false,
			breakpointMobile = 768,
			i = 0;

		$(\'.hero-pagebuilder-slider\').on(\'init breakpoint\', function(event, slick){
			$(\'.hide-on-mobile\').parents(\'.slick-slide\').addClass(\'hide-on-mobile\');
			$(\'.hide-on-desktop\').parents(\'.slick-slide\').addClass(\'hide-on-desktop\');			

			if (!isChanging && i == 0) {
				isChanging = true;

				if (slick.activeBreakpoint && slick.activeBreakpoint <= breakpointMobile) {
					i++;
					slick.slickFilter(\':not(.hide-on-mobile)\');
				} else {
					i++;
					slick.slickFilter(\':not(.hide-on-desktop)\');
				}

				isChanging = false;
			}
		}).slick({
			autoplay: true,
			dots: true,
			responsive: [
				{breakpoint: 768}
			]
		});
    });
</script></div></div>',
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