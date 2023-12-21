<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\Patch\DataPatchInterface;

class AddTemplatesData implements DataPatchInterface
{
    /**
     * @var \Magento\PageBuilder\Model\TemplateFactory
     */
     private $templateFactory;

    /**
     * @param \Magento\PageBuilder\Model\TemplateFactory $templateFactory
     */
     public function __construct(
          \Magento\PageBuilder\Model\TemplateFactory $templateFactory
     ) {
          $this->templateFactory = $templateFactory;
     }

     public function apply()
     {
          $sampleData = [
               [
                    'name' => 'hk-laneige-main-hero-banner-slider-production',
                    'preview_image' => '.template-managerhklaneigemainherobannersliderproduction657fe611c4657.jpg',
                    'template' => '<style>#html-body [data-pb-style=D80ENNL]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin-bottom:80px}#html-body [data-pb-style=NW91V2S]{margin:0;padding:0}#html-body [data-pb-style=HHPW10N]{border-style:none;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=MSEYPUP]{border-radius:0;margin:0;padding:0}#html-body [data-pb-style=MFSGM5K]{text-align:left;min-height:540px;border-style:none;border-radius:0;margin:10px 0 0;padding:0}#html-body [data-pb-style=C1VT5AH]{margin:0}#html-body [data-pb-style=G6TNHRI]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=YQPXW2S]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=RQB0S0Y]{opacity:1;visibility:visible}#html-body [data-pb-style=VBOIP4H]{margin:0}#html-body [data-pb-style=IAH3O3A]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=X9UPB7X]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=TYDBUE2]{opacity:1;visibility:visible}#html-body [data-pb-style=APNL5YN]{margin:0}#html-body [data-pb-style=KMXF3LB]{background-position:center center;background-size:cover;background-repeat:no-repeat;border-style:none;border-radius:0;min-height:540px}#html-body [data-pb-style=REII6W3]{min-height:540px;padding:0;background-color:transparent}#html-body [data-pb-style=M0NI1DI]{opacity:1;visibility:visible}</style><div class="main-hero-slider" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="D80ENNL"><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="NW91V2S"><script type="text/javascript" xml="space">
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
</script></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="HHPW10N"><script type="text/javascript" xml="space">
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
</style></div><div class="pagebuilder-slider hero-pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="true" data-autoplay-speed="4000" data-fade="true" data-infinite-loop="true" data-show-arrows="true" data-show-dots="true" data-element="main" data-pb-style="MFSGM5K"><div class="btn_white header_center" data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="C1VT5AH"><a href="https://tw.laneige.com/skincare/line/perfect-renew.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/20231213-_BN_PC_2880x810_.jpg}}\",\"mobile_image\":\"{{media url=wysiwyg/20231213-_BN_MO_720x540_.jpg}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="G6TNHRI"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="網店獨家迎新禮遇" title="" data-element="overlay" data-pb-style="YQPXW2S"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>完美新生三效賦活精華 #三管精華</strong></h2><div></div><div id="Y76FESP" class="ewa-rteLine"><span style="font-size: 16px;">3種精華集成一瓶，快速、準確、強效，</span></div><div class="ewa-rteLine"><span style="font-size: 16px;">精準狙擊肌膚老化困擾，有效提升肌膚光彩、肌底彈性、撫平皺紋。</span></div></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="RQB0S0Y">了解詳情</button></div></div></div></a></div><div class="btn_white header_center" data-content-type="slide" data-slide-name="氣墊王者！NEO型塑霧感/光感氣墊EX" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="VBOIP4H"><a href="https://tw.laneige.com/make-up/face/cushion.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/cushion_PC_2880_810.jpg}}\",\"mobile_image\":\"{{media url=wysiwyg/cushion_MO_720_540_1.jpg}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="IAH3O3A"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="網店獨家迎新禮遇" title="" data-element="overlay" data-pb-style="X9UPB7X"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>氣墊王者！NEO型塑霧感/光感氣墊EX</strong></h2><div></div><div id="Y76FESP" class="ewa-rteLine"><div id="Y76FESP" class="ewa-rteLine"><span style="font-size: 16px;">蘭芝首創3大超革新技術！NEO無痕貼膚科技，50小時保濕透亮，更輕盈服貼不沾染。獨家FAM緊緻透亮科技，一拍如薄荷般清爽的微霧光NEO妝容</span><span style="font-size: 16px;">。</span></div></div></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="TYDBUE2">了解詳情</button></div></div></div></a></div><div class="btn_white h3_header mobile_slide_center150 slide_bottom" data-content-type="slide" data-slide-name="Merry Sweet Holiday！" data-appearance="poster" data-show-button="always" data-show-overlay="always" data-element="main" data-pb-style="APNL5YN"><a href="https://tw.laneige.com/best-new/vbom.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{\"desktop_image\":\"{{media url=wysiwyg/holiday_pc_2880x810_1.png}}\",\"mobile_image\":\"{{media url=wysiwyg/holiday_mo_720x540_1.png}}\"}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="KMXF3LB"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="會員禮遇" title="" data-element="overlay" data-pb-style="REII6W3"><div class="pagebuilder-poster-content"><div data-element="content"><h2><strong>Merry Sweet Holiday！</strong></h2><div></div><div><span style="font-size: 16px;">蘭芝首創3大超革新技術！NEO無痕貼膚科技，50小時保濕透亮，更輕盈服貼不沾染。獨家FAM緊緻透亮科技，一拍如薄荷般清爽的微霧光NEO妝容。</span></div><h2 style="line-height: 40px;"><span style="color: #000000; font-size: 18px; font-family: \'Noto Sans\', sans-serif; background-color: transparent;">​</span></h2></div><button type="button" class="pagebuilder-slide-button pagebuilder-button-link" data-element="button" data-pb-style="M0NI1DI">馬上逛逛</button></div></div></div></a></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
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

</style></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="MSEYPUP"><script type="text/javascript" xml="space">
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'new_arrivals_hk_laneige-prod',
                    'preview_image' => '.template-managernewarrivalshklaneigeproduction657fe71782f87.jpg',
                    'template' => '<style>#html-body [data-pb-style=DOGVI81]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=KAUGCTC]{text-align:center}#html-body [data-pb-style=KJFMA59]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=MIYW3UD]{display:flex;width:100%}#html-body [data-pb-style=CDCNL1G]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;padding-right:10px;align-self:stretch}#html-body [data-pb-style=V2F7RYG]{text-align:left}#html-body [data-pb-style=UWRQFN9]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;padding-left:10px;align-self:stretch}#html-body [data-pb-style=H0ML0CN]{border-style:none}#html-body [data-pb-style=KDJAJLG],#html-body [data-pb-style=NYJWHYT]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=H0ML0CN]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="new_arrivals_section margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="DOGVI81"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="KAUGCTC">最新推薦</h2><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="KJFMA59"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="MIYW3UD"><div class="pagebuilder-column banner-newarrivals" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="CDCNL1G"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="H0ML0CN"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-2_B_1080px_788px_1.png}}" alt="禦時緊顏參養煥白系列" title="" data-element="desktop_image" data-pb-style="NYJWHYT"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-2_B_640px_1.jpg}}" alt="禦時緊顏參養煥白系列" title="" data-element="mobile_image" data-pb-style="KDJAJLG"></figure><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="V2F7RYG"><div class="banner-posstion-left new-product-session">
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
</style></div></div><div class="pagebuilder-column product-newarrivals" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="UWRQFN9"><div class="product-new-arrivals" data-content-type="products" data-appearance="grid" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="Magento_CatalogWidget::product/widget/content/grid.phtml" anchor_text="" id_path="" show_pager="0" products_count="2" condition_option="sku" condition_option_value="simple_222,simple_111" type_name="Catalog Products List" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`simple_222,simple_111`^]^]" sort_order="position_by_sku"}}</div><div data-content-type="html" data-appearance="default" data-element="main"><script type="text/javascript" xml="space">
    require([\'jquery\',\'swiper\',\'matchMedia\'],function($,Swiper,mediaCheck){
        $(document).ready(function() {

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

</style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-laneige-main-bestseller-slider-production',
                    'preview_image' => '.template-managerhklaneigemainbestsellersliderproduction657fe7aced524.jpg',
                    'template' => '<style>#html-body [data-pb-style=MU59HR0]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=X92G1QE]{text-align:center}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="MU59HR0"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="X92G1QE">暢銷產品</h2><div data-content-type="html" data-appearance="default" data-element="main"><div class="bestseller_home_content">
                    <div class="bestseller_tab main-recommendation-slider">
                      <div class="product data items" data-mage-init=\'{"tabs":{"openedState":"active"}}\'>
              <div class="tab-head_titles">
                        <div class="tab-titles">
                          <div class="data item title" data-role="collapsible" id="tab-label-all">
                            <a class="data switch"
                               tabindex="-1"
                               data-toggle="trigger"
                               href="#bestseller_tab_all"
                               id="tab-label-bestseller_tab_all-title">
                                精華
                            </a>
                          </div>
                          <div class="data item title" data-role="collapsible" id="tab-label-emulsion">
                            <a class="data switch"
                               tabindex="-1"
                               data-toggle="trigger"
                               href="#bestseller_tab_emulsion"
                               id="tab-label-bestseller_tab_emulsion-title">
                                面霜
                            </a>
                          </div>
                          <div class="data item title" data-role="collapsible" id="tab-label-eyeliner">
                              <a class="data switch"
                                 tabindex="-1"
                                 data-toggle="trigger"
                                 href="#bestseller_tab_eyeliner"
                                 id="tab-label-bestseller_tab_eyeliner-title">
                                  眼部護理
                              </a>
                            </div>
                        </div>
              </div>
                        <div class="tab-contents">
                          <div class="data item content bestseller-contents-slider"
                             aria-labelledby="tab-label-bestseller_tab_all-title" id="bestseller_tab_all" data-role="content">
              
              {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" id="bestseller_tab_serum_widget" show_pager="0" products_count="10" cache_lifetime="0" progress="false" mobile="1" desktop="4" template="product/widget/content/grid-slide.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_222`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_111`^],`1--3`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`111975629-1-1`^],`1--4`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`111989323`^]^]"}}
              
                          </div>
                          <div class="data item content bestseller-contents-slider"
                               aria-labelledby="tab-label-bestseller_tab_emulsion-title" id="bestseller_tab_emulsion" data-role="content">
              {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" id="bestseller_tab_emulsion_widget" show_pager="0" products_count="10" cache_lifetime="0" progress="false" mobile="1" desktop="4" template="product/widget/content/grid-slide.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`111975603-3`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`111989323`^],`1--3`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_222`^]^]"}}
              
              
                          </div>
                          <div class="data item content bestseller-contents-slider"
                               aria-labelledby="tab-label-bestseller_tab_eyeliner-title" id="bestseller_tab_eyeliner" data-role="content">
                               {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" id="bestseller_tab_eyeliner_widget" show_pager="0" products_count="8" cache_lifetime="0" progress="false" mobile="1" desktop="4" template="product/widget/content/grid-slide.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`BRANDSR-5265_TEST`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`111975603-3`^],`1--3`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`TEST20231025`^]^]"}}
              
              
              
              
                          </div>
                        </div>
                      </div>
                    </div>
              </div>
              <script type="text/javascript">
                      require([\'jquery\', \'slick\'], function($, slick) {
                          $(document).ready(function() {
                              $(\'.bestseller-contents-slider\').each(function(index, element) {
                                  var $prdRecomm =  $(this).find(\'.product-items\'),
                                      $prdRecommProgressBar = $(this).find(\'.progress .progress_ing\'),
                                      $prdRecommSlidesToShow = 4;
                                  
                                  $prdRecomm.on(\'init\', function(event, slick){
                                      (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.5;
                                      var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow));
              
                                      $prdRecommProgressBar.css({
                                          \'width\': calc + \'%\',
                                      });
                                  });
              
                                  $prdRecomm.slick({
                                      dots:false,
                                      slidesToShow: $prdRecommSlidesToShow,
                                      slidesToScroll: $prdRecommSlidesToShow,
                                      infinite: false,
                                      responsive: [
                                          {
                                              breakpoint: 768,
                                              settings: {
                                                  dots: true,autoplay: true,
                                                  slidesToShow: 1,
                                                  slidesToScroll: 1,
                                                  prevArrow: false,
                                                  nextArrow: false
                                              }
                                          },
                                      ]
                                  }).on(\'beforeChange\', function(event, slick, currentSlide, nextSlide){
                                      (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.5;
                                      var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow)) * ((nextSlide / $prdRecommSlidesToShow) + 1);
              
                                      $prdRecommProgressBar.css({
                                          \'width\': calc + \'%\',
                                      });
                                  });
                              });
                          })
                      });
                  </script>
              <style>
                .main-recommendation-slider {
                text-align: center;
              }
              .main-recommendation-slider .tab-contents {
                position: relative;
              }
              .main-recommendation-slider .tab-titles {
                margin: 0 auto 40px !important;
                text-align: left;
                border-bottom: 1px solid #ddd;
                width: auto;
                display: inline-block;
              }
              
              .main-recommendation-slider .product.data.items .item.title {
                width: auto;
                float: none;
                margin-bottom: 0;
                display: inline-block;
              }
              .main-recommendation-slider .product.data.items .item.title .data.switch {
                padding: 0 24px;
              text-transform: capitalize;
              color: #969696;
              font-size: 18px;
              line-height: 42px;
              height: 42px;
              border-bottom: 2px solid #fff;
              display: inline-block;
              }
              .main-recommendation-slider .product.data.items .item.title.active .data.switch {
                color: #222;
                font-weight: 500;
                border-bottom-color: #222;
              }
              .main-recommendation-slider .tab-contents .data.content {
                display: block !important;
                opacity: 0;
                visibility: hidden;
                height: 0;
              }
              .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {
                visibility: visible;
                opacity: 1;
                height: auto;
              }
              @media (max-width: 767px){
              .bestseller_home_content .main-recommendation-slider {margin: 0;}
              .bestseller_home_content .main-recommendation-slider .tab-head_titles {margin-left: 20px;}
              .bestseller_home_content .main-recommendation-slider .tab-contents {margin: 0 20px;overflow: hidden;padding: 0 10px 2px;}
              .bestseller_home_content .main-recommendation-slider .tab-contents .progress {bottom: 3px;}
              .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .slick-track {
                  margin: 0;
              }
              .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .products.product-items .product-item {
                  padding: 0;
              }
              }
              </style></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
              
                  .product-items .action.towishlist:not(.updated):before, .products.list.items .action.towishlist:not(.updated):before {
                      padding: 10px 10px 20px 20px;
                  }
                  .bestseller-contents-slider .progress{
                     display:none;
                  }
                  .products.list.items .action.towishlist {
                      top:0;
                      right:0;
                  }   
              @media (max-width: 767px){ 
                  .products .products.list.items .action.towishlist, .products.list.items .action.towishlist {
                      top: 0px;
                      right:0;
                  }
              .bestseller_home_content .tab-head_titles {
              overflow:hidden;
              }
              }
              .block.widget.block-products-slider .products.product-items .slick-list .slick-slide {
                  margin: 0 10px;
                  user-select: auto !important;
              }
              .slick-slider {
              user-select: auto !important;
              }
              </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-laneige-banner_hasellus-production',
                    'preview_image' => '.template-managerhklaneigebannerhasellusproduction657fe7d9a3e9a.jpg',
                    'template' => '<style>#html-body [data-pb-style=KEU3CL3]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=G1D9C0T]{border-style:none}#html-body [data-pb-style=EVPD7IU],#html-body [data-pb-style=KSLRDIQ]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=G1D9C0T]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="banner-newarrivals banner_hasellus margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="KEU3CL3"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="G1D9C0T"><a href="/value-set.html" target="" data-link-type="default" title="hasellus" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-1-4_2010x518__1.png}}" alt="hasellus" title="hasellus" data-element="desktop_image" data-pb-style="EVPD7IU"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-1-4_720x360_.png}}" alt="hasellus" title="hasellus" data-element="mobile_image" data-pb-style="KSLRDIQ"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="banner-posstion-left">
                    <h3>早C晚A每日B 透亮細緻水潤肌</h3>
                    <!--<p>多款皇牌養肌套裝，為你新年煥新肌。</p>-->
                    <a href="/value-set.html" class="btn-viewmore">立即選購</a>
                    </div>
                    <style>
                    .banner-posstion-left p, .banner-posstion-left .btn-viewmore {
                       color: white;
                    }
                    .banner_hasellus .banner-posstion-left h3 {
                       color: white;
                    }
                    @media only screen and (min-width: 768px) and (max-width: 1280px) {
                    .banner-posstion-left .btn-viewmore {
                        margin-top: 5px;
                    }
                    }
                    @media only screen and (min-width: 768px) and (max-width: 1024px) {
                    . banner_hasellus figure[data-content-type=\'image\'] img {
                        min-height: 240px;
                        width: 100%;
                        object-fit: cover;
                    }
                    }
                    @media only screen and (max-width: 767px){
                     .banner_hasellus {
                       margin: 0 20px;
                     }
                    .banner-posstion-left p, .banner-posstion-left .btn-viewmore {
                       color: black;
                    }
                     .banner_hasellus .banner-posstion-left h3 {
                       color: black;
                     }
                    }
                    
                    </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-laneige-main-giftset-slider-production',
                    'preview_image' => '.template-managerhklaneigemaingiftsetsliderproduction657fe80c1e3d4.jpg',
                    'template' => '<style>#html-body [data-pb-style=RQXQW6U]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=UHW5W0J]{text-align:center}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="RQXQW6U"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="UHW5W0J">限時組合</h2><div data-content-type="html" data-appearance="default" data-element="main"><div class="giftset_home_content giftset_tab_content">
                    <div class="giftset_tab main-recommendation-slider">
                        <div class="product data items" data-mage-init=\'{"tabs":{"openedState":"active"}}\'>
                <div class="tab-head_titles">
                
                {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" title="限時套裝" show_pager="1" products_per_page="4" products_count="10" template="Sapt_AjaxWishlist::product/widget/content/slider.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_222,simple_111,`^]^]" page_var_name="piurma"}}
                            <!--<div class="tab-titles">
                                <div class="data item title" data-role="collapsible" id="tab-label-giftset">
                                    <a class="data switch"
                                       tabindex="-1"
                                       data-toggle="trigger"
                                       href="#giftset_tab_all"
                                       id="tab-label-giftset_tab_all-title">
                                        基礎系列
                                    </a>
                                </div>
                                <div class="data item title" data-role="collapsible" id="tab-label-skin">
                                    <a class="data switch"
                                       tabindex="-1"
                                       data-toggle="trigger"
                                       href="#gifset_tab_skin"
                                       id="tab-label-gifset_tab_skin-title">
                                        人參系列
                                    </a>
                                </div>
                                <div class="data item title" data-role="collapsible" id="tab-label-perfect">
                                    <a class="data switch"
                                       tabindex="-1"
                                       data-toggle="trigger"
                                       href="#giftset_tab_perfect"
                                       id="tab-label-giftset_tab_perfect-title">
                                        1＋1養肌組合
                                    </a>
                                </div>
                            </div>-->
                </div>
                
                            <div class="data item content giftset-contents-slider">
                
                {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" progress="false" mobile="1.5" desktop="4" template="product/widget/content/grid-slide.phtml" cache_lifetime="0" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_111`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_222`^],`1--3`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`TEST20231025`^]^]"}}
                            </div>
                
                            <!--<div class="tab-contents">
                                <div class="data item content giftset-contents-slider"
                                     aria-labelledby="tab-label-giftset_tab_all-title" id="giftset_tab_all" data-role="content">
                
                                </div>
                                <div class="data item content giftset-contents-slider"
                                     aria-labelledby="tab-label-gifset_tab_skin-title" id="gifset_tab_skin" data-role="content">
                
                                </div>
                                <div class="data item content giftset-contents-slider"
                                     aria-labelledby="tab-label-giftset_tab_perfect-title" id="giftset_tab_perfect" data-role="content">
                                </div>
                            </div>-->
                        </div>
                    </div>
                </div>
                <script type="text/javascript">
                        require([\'jquery\', \'slick\'], function($, slick) {
                            $(document).ready(function() {
                                $(\'.giftset-contents-slider\').each(function(index, element) {
                                    var $prdRecomm =  $(this).find(\'.product-items\'),
                                        $prdRecommProgressBar = $(this).find(\'.progress .progress_ing\'),
                                        $prdRecommSlidesToShow = 4;
                                    
                                    $prdRecomm.on(\'init\', function(event, slick){
                                        (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.56;
                                        var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow));
                
                                        $prdRecommProgressBar.css({
                                            \'width\': calc + \'%\',
                                        });
                                    });
                
                                    $prdRecomm.slick({
                                        dots:false,
                                        slidesToShow: $prdRecommSlidesToShow,
                                        slidesToScroll: $prdRecommSlidesToShow,
                                        infinite: false,
                                        responsive: [
                                            {
                                                breakpoint: 768,
                                                settings: {
                                                    dots: true,
                                                    autoplay: true,
                                                    slidesToShow: 1.56,
                                                    slidesToScroll: 1,
                                                    prevArrow: false,
                                                    nextArrow: false
                                                }
                                            },
                                        ]
                                    }).on(\'beforeChange\', function(event, slick, currentSlide, nextSlide){
                                        (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.56;
                                        var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow)) * ((nextSlide / $prdRecommSlidesToShow) + 1);
                
                                        $prdRecommProgressBar.css({
                                            \'width\': calc + \'%\',
                                        });
                                    });
                                });
                            })
                        });
                    </script>
                <style>
                .main-recommendation-slider {text-align: center;}
                .main-recommendation-slider .tab-contents {position: relative;}
                .main-recommendation-slider .tab-titles {margin: 0 auto 40px !important;text-align: left;border-bottom: 1px solid #ddd;width: auto;display: inline-block;}
                
                .main-recommendation-slider .product.data.items .item.title {width: auto;float: none;margin-bottom: 0;display: inline-block;}
                .main-recommendation-slider .product.data.items .item.title .data.switch {padding: 0 24px;text-transform: capitalize;color: #969696;font-size: 18px;line-height: 42px;height: 42px;border-bottom: 2px solid #fff;display: inline-block;}
                .main-recommendation-slider .product.data.items .item.title.active .data.switch {color: #222;font-weight: 500;border-bottom-color: #222;}
                .main-recommendation-slider .tab-contents .data.content {display: block !important;opacity: 0;visibility: hidden;height: 0;}
                .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {visibility: visible;opacity: 1;height: auto;}
                .giftset_home_content .block.widget.block-products-slider .progress, .giftset_home_content .block.widget.block-products-slider .slick-pause {
                  display: none;
                }
                @media(min-width: 768px){
                    .main-recommendation-slider .block.widget.block-products-slider .progress {display: none;}
                }
                @media(max-width: 767px){
                    .main-recommendation-slider {margin-left: 20px;}
                    .main-recommendation-slider .tab-titles {border: 0;margin: 0 auto 15px !important;width: 767px;white-space: nowrap;}
                    .main-recommendation-slider .product.data.items .item.title .data.switch {font-size: 14px;line-height: 30px;height: 30px;}
                    .main-recommendation-slider .product.data.items {margin: 0;}
                    .main-recommendation-slider .product.data.items .item.title {margin: 0 16px 0 0;}
                    .main-recommendation-slider .product.data.items .item.title .data.switch {padding: 0;}
                    .main-recommendation-slider .block.widget.block-products-slider .products.product-items {margin: 0;}
                    .main-recommendation-slider .product-image-container {width: 100% !important;}
                    .main-recommendation-slider .block.widget.block-products-slider .products.product-items .product-item {padding: 0 6px;}
                    .main-recommendation-slider .block.widget.block-products-slider .slick-track {margin: 0 -6px;}
                    .main-recommendation-slider .block.widget.block-products-slider .products-grid {padding-bottom: 24px;}
                    .main-recommendation-slider .block.widget.block-products-slider .progress {width: 110px;left: calc(50% - 15px);margin-right: 30px;}
                    .tab-head_titles {overflow-x: auto;}
                    .main-recommendation-slider .product.data.items .item.content {border: 0;}
                    .main-recommendation-slider .block.widget.block-products-slider .slick-dots {display: none !important;}
                    .main-recommendation-slider .block.widget.block-products-slider .slick-pause {position: absolute;bottom: -38px;top: initial;right: calc(50% - 70px);}
                    .main-recommendation-slider .block.widget.block-products-slider .slick-pause::after {font-size: 10px;line-height: 20px;content: "\e812";font-family: \'fontello\';color: #222;}
                    .main-recommendation-slider .block.widget.block-products-slider .slick-pause.active::after {content: "\e813";}
                    .giftset-contents-slider .block.widget.block-products-slider .products.product-items .slick-list {margin: 0;}
                }
                </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk_laneige_recommended_categories-production',
                    'preview_image' => '.template-managerhklaneigerecommendedcategoriesproduction657fe8672f732.jpg',
                    'template' => '<style>#html-body [data-pb-style=UMOWVU0]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin-left:-15px;margin-right:-15px}#html-body [data-pb-style=O239KQM]{text-align:center}#html-body [data-pb-style=KFSBN7F]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=Y8D9QBV]{display:flex;width:100%}#html-body [data-pb-style=LFJ4Y4L]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=RVJWJD0]{text-align:center}#html-body [data-pb-style=R4DIJ0S]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=QC7CMKC]{text-align:center}#html-body [data-pb-style=Q5D8W9J]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;padding-left:15px;padding-right:15px;align-self:stretch}#html-body [data-pb-style=PYA8VO3]{text-align:center}#html-body [data-pb-style=P8AT0N5]{border-style:none}#html-body [data-pb-style=EC2CHQT],#html-body [data-pb-style=HS55JDP]{max-width:100%;height:auto}#html-body [data-pb-style=JO10OE5]{border-style:none}#html-body [data-pb-style=DT1KWQL],#html-body [data-pb-style=V1C0BPM]{max-width:100%;height:auto}#html-body [data-pb-style=T98478G]{border-style:none}#html-body [data-pb-style=MWDY5QM],#html-body [data-pb-style=NO71CS1]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=JO10OE5],#html-body [data-pb-style=P8AT0N5],#html-body [data-pb-style=T98478G]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="recommended_categories margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="UMOWVU0"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="O239KQM">推薦系列</h2><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="KFSBN7F"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="Y8D9QBV"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="LFJ4Y4L"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="P8AT0N5"><a href="/skincare.html" target="" data-link-type="default" title="護膚" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_A_990__1.jpg}}" alt="護膚" title="護膚" data-element="desktop_image" data-pb-style="HS55JDP"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_A_990__2.jpg}}" alt="護膚" title="護膚" data-element="mobile_image" data-pb-style="EC2CHQT"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="RVJWJD0">完美新生三效系列</h3></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="R4DIJ0S"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="JO10OE5"><a href="/value-set.html" target="" data-link-type="default" title="禮品套裝" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_B_990__2.jpg}}" alt="禮品套裝" title="禮品套裝" data-element="desktop_image" data-pb-style="V1C0BPM"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_B_990__1.jpg}}" alt="禮品套裝" title="禮品套裝" data-element="mobile_image" data-pb-style="DT1KWQL"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="QC7CMKC">水酷修護保濕系列</h3></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="Q5D8W9J"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="T98478G"><a href="{{widget type=\'Magento\Catalog\Block\Category\Widget\Link\' id_path=\'category/1031\' template=\'Magento_PageBuilder::widget/link_href.phtml\' type_name=\'Catalog Category Link\' }}" target="" data-link-type="category" title="人參系列" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_C_990__1.jpg}}" alt="人參系列" title="人參系列" data-element="desktop_image" data-pb-style="NO71CS1"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_C_990__2.jpg}}" alt="人參系列" title="人參系列" data-element="mobile_image" data-pb-style="MWDY5QM"></a></figure><h3 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="PYA8VO3">NEO型塑系列</h3></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><script type="text/javascript" xml="space">
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-laneige-main-post-production',
                    'preview_image' => '.template-managerhklaneigemainpostproduction657fe8a95e59a.jpg',
                    'template' => '<style>#html-body [data-pb-style=YPVRMDA]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;margin:0;padding:0 0 70px}#html-body [data-pb-style=WFUA044]{justify-content:flex-start;display:flex;flex-direction:column;margin:0;padding:0 0 100px}#html-body [data-pb-style=AD5P78Y],#html-body [data-pb-style=RRSKXCJ],#html-body [data-pb-style=WFUA044],#html-body [data-pb-style=X26XL29]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=AD5P78Y]{justify-content:flex-start;display:flex;flex-direction:column}#html-body [data-pb-style=RRSKXCJ],#html-body [data-pb-style=X26XL29]{align-self:stretch}#html-body [data-pb-style=NBNEIN9],#html-body [data-pb-style=RN4A5HB]{display:flex;width:100%}#html-body [data-pb-style=ANUHV62]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;text-align:right;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=DMSMVY2]{text-align:center}#html-body [data-pb-style=MJ7FU4D],#html-body [data-pb-style=N21GQUB],#html-body [data-pb-style=SG7IJ3E]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;margin:0;padding:0;align-self:center}#html-body [data-pb-style=N21GQUB],#html-body [data-pb-style=SG7IJ3E]{text-align:left}#html-body [data-pb-style=WRDEQ8N]{text-align:center}#html-body [data-pb-style=T92CJYC]{text-align:center;margin-top:50px}#html-body [data-pb-style=C6LJDI3],#html-body [data-pb-style=NHUTY86],#html-body [data-pb-style=RM3H2N5],#html-body [data-pb-style=VP9T7X9]{text-align:center}#html-body [data-pb-style=TN74K19]{text-align:right;border-style:none}#html-body [data-pb-style=AISC3YR],#html-body [data-pb-style=DARVLA5]{max-width:100%;height:auto}#html-body [data-pb-style=CU8XF51]{text-align:right;border-style:none}#html-body [data-pb-style=WY7TCOL],#html-body [data-pb-style=YVLUEB5]{max-width:100%;height:auto}#html-body [data-pb-style=V461YVT]{text-align:center;margin-top:50px}#html-body [data-pb-style=BKR8W4H]{text-align:center;display:none;margin-top:50px}#html-body [data-pb-style=SOV8PAF]{display:inline-block}#html-body [data-pb-style=QGKX7NF]{text-align:center;margin-bottom:0;padding-left:36px;padding-right:36px}#html-body [data-pb-style=WVLPTL9]{display:inline-block}#html-body [data-pb-style=BVROWBO]{text-align:center;margin-bottom:0;padding-left:36px;padding-right:36px}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CU8XF51],#html-body [data-pb-style=TN74K19]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="lounge-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="YPVRMDA"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="X26XL29"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="RN4A5HB"><div class="pagebuilder-column lounge-text" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="ANUHV62"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="NHUTY86">完美新生三效賦活精華</h2><h4 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="RM3H2N5">三管精華 三管精準抗老</h4><div class="block-content-recom" data-content-type="text" data-appearance="default" data-element="main"><p style="text-align: center;"><span style="font-size: 18px;">3種精華集成一瓶，快速、準確、強效</span></p></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="DMSMVY2"><div class="product_tags block-content-recom">精準狙擊肌膚老化困擾，有效提升肌膚光彩、
肌底彈性、撫平皺紋</div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="V461YVT"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="SOV8PAF"><a class="pagebuilder-button-secondary" href="concentrated-ginseng-renewing-serum-ex.html" target="" data-link-type="default" data-element="link" data-pb-style="QGKX7NF"><span data-element="link_text">了解更多</span></a></div></div></div><div class="pagebuilder-column image-odd" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="MJ7FU4D"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="TN74K19"><a href="/experience/archive/etc_concentrated_ginseng_renewing_serum_2022" target="" data-link-type="default" title="皇牌人參安瓶" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_D_990px__1.jpg}}" alt="皇牌人參安瓶" title="皇牌人參安瓶" data-element="desktop_image" data-pb-style="AISC3YR"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_D_990px__2.jpg}}" alt="皇牌人參安瓶" title="皇牌人參安瓶" data-element="mobile_image" data-pb-style="DARVLA5"></a></figure></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div class="lounge-row" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="WFUA044"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="RRSKXCJ"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="NBNEIN9"><div class="pagebuilder-column image-odd" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="SG7IJ3E"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CU8XF51"><a href="/brand-belief.html" target="" data-link-type="default" title=" skin recovery" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-5-8_E_990px__1.jpg}}" alt=" skin recovery" title=" skin recovery" data-element="desktop_image" data-pb-style="WY7TCOL"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-5-8_E_990px__2.jpg}}" alt=" skin recovery" title=" skin recovery" data-element="mobile_image" data-pb-style="YVLUEB5"></a></figure></div><div class="pagebuilder-column lounge-text" data-content-type="column" data-appearance="align-center" data-background-images="{}" data-element="main" data-pb-style="N21GQUB"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="C6LJDI3">品牌故事</h2><h4 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="VP9T7X9">我耀我的光</h4><div class="block-content-recom" data-content-type="text" data-appearance="default" data-element="main"><p style="text-align: center;"><span style="font-size: 18px;">from skin to my life. 「LANEIGE」，法文中的「雪」， 意指美的永恆定義－如雪般飽水淨透的無瑕肌膚。</span></p></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="WRDEQ8N"><div class="product_tags block-content-recom">自1994年創立，28年來蘭芝精研肌膚科學，
時間的腳步從未停止，肌膚的狀態也細微的不停地改變著。
FEEL the GLOW  with LANEIGE.</div></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="T92CJYC"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="OMYOK0T"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="QLODO44"><a class="pagebuilder-button-secondary" href="/brand-story" target="" data-link-type="default" data-element="link" data-pb-style="S72461O"><span data-element="link_text">了解更多</span></a></div></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="BKR8W4H"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="WVLPTL9"><a class="pagebuilder-button-secondary" href="brand-belief.html" target="" data-link-type="default" data-element="link" data-pb-style="BVROWBO"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="AD5P78Y"><div data-content-type="html" data-appearance="default" data-element="main"><style>
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk_laneige_spa_main-production',
                    'preview_image' => '.template-managerhklaneigespamainproduction657fe941e4d57.jpg',
                    'template' => '<style>#html-body [data-pb-style=H6M5PXG]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=RYX268H],#html-body [data-pb-style=X7RQXYB]{text-align:center}#html-body [data-pb-style=CFN7AMR]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=P68X444]{display:flex;width:100%}#html-body [data-pb-style=D1CQTLS],#html-body [data-pb-style=DIDPH65],#html-body [data-pb-style=G7D40SA]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:33.3333%;align-self:stretch}#html-body [data-pb-style=CMHDWAU]{border-style:none}#html-body [data-pb-style=RO7H4RS],#html-body [data-pb-style=TRVWG24]{max-width:100%;height:auto}#html-body [data-pb-style=S45F207]{border-style:none}#html-body [data-pb-style=F9Q0EPK],#html-body [data-pb-style=MPK05S5]{max-width:100%;height:auto}#html-body [data-pb-style=OMQ0B52]{border-style:none}#html-body [data-pb-style=AH6ER1T],#html-body [data-pb-style=R1KKJKW]{max-width:100%;height:auto}@media only screen and (max-width: 768px) { #html-body [data-pb-style=CMHDWAU],#html-body [data-pb-style=OMQ0B52],#html-body [data-pb-style=S45F207]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140 home-spa" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="H6M5PXG"><h2 data-content-type="heading" data-appearance="default" data-element="main" data-pb-style="RYX268H">《I\'m LANEIGE》雜誌</h2><div class="sub-title" data-content-type="text" data-appearance="default" data-element="main" data-pb-style="X7RQXYB"><p style="text-align: center;"><span style="font-size: 18px;">韓系皮膚管理，保養美妝秘笈</span></p></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="CFN7AMR"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="P68X444"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="D1CQTLS"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="CMHDWAU"><a href="/spa/introduction.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-A_660x396_1.jpg}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="TRVWG24"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-A_660x396_2.jpg}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="RO7H4RS"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
<p>抗老保養過程中，4個不能小看的老化原因！想要立即擁有完美新生這樣做</p>
<a href="https://tw.laneige.com/brand/new-magazine/aging.html" class="btn-viewmore">了解更多</a>
</div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="G7D40SA"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="S45F207"><a href="/spa/membership.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-B_660x396_1.png}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="F9Q0EPK"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-B_660x396_2.png}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="MPK05S5"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
<p>想要擁有無瑕水嫩的無齡膚質，保濕保養程序步驟不可少！想知道你的保濕方法正不正確 </p>
<a href="https://tw.laneige.com/brand/new-magazine/recommendations-for-moisturizing-products.html" class="btn-viewmore">了解更多</a>
</div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="DIDPH65"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="OMQ0B52"><a href="/spa/beauty-lounge.html" target="" data-link-type="default" title="spa" data-element="link"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D003-9-C_660x396_1.jpg}}" alt="spa" title="spa" data-element="desktop_image" data-pb-style="R1KKJKW"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D003-9-C_660x396_2.jpg}}" alt="spa" title="spa" data-element="mobile_image" data-pb-style="AH6ER1T"></a></figure><div data-content-type="html" data-appearance="default" data-element="main"><div class="home-spa-detail">
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk_laneige_membership_only_home-production',
                    'preview_image' => '.template-managerhklaneigemembershiponlyhomeproduction657fe9a3c0717.jpg',
                    'template' => '<style>#html-body [data-pb-style=P4U69FH],#html-body [data-pb-style=WOTVEFN]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=WOTVEFN]{justify-content:flex-start;display:flex;flex-direction:column;background-color:#ddd7cc;margin-top:140px;padding-top:80px;padding-bottom:80px}#html-body [data-pb-style=P4U69FH]{align-self:stretch}#html-body [data-pb-style=EPY4I8C]{display:flex;width:100%}#html-body [data-pb-style=ACRB9HG],#html-body [data-pb-style=OJPDXW1]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=OJPDXW1]{width:41.6667%;padding-left:80px}#html-body [data-pb-style=ACRB9HG]{width:58.3333%}#html-body [data-pb-style=U8HSU72]{display:none}#html-body [data-pb-style=LR3JIS9]{display:inline-block}#html-body [data-pb-style=MAILVA2]{text-align:center;margin-top:40px}</style><div class="home-membership" data-content-type="row" data-appearance="full-width" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="WOTVEFN"><div class="row-full-width-inner" data-element="inner"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="P4U69FH"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="EPY4I8C"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="OJPDXW1"><h2 data-content-type="heading" data-appearance="default" data-element="main">會員獨享 </h2><div data-content-type="text" data-appearance="default" data-element="main"><p><span style="font-size: 18px;">成為台灣蘭芝會員，優先獲取品牌最新消息，並享有會員禮遇。</span></p></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-view" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="KDMPHYE"><a class="membership-link pagebuilder-button-secondary" href="https://tw.laneige.com/special-offers/membership.html" target="" data-link-type="default" data-element="link" data-pb-style="K5BE2Y6"><span data-element="link_text">了解更多</span></a></div></div></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="U8HSU72"><div class="btn-view" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="LR3JIS9"><a class="pagebuilder-button-secondary" href="membership/online-shop-exclusive-privileges.html" target="" data-link-type="default" data-element="link" data-pb-style="MAILVA2"><span data-element="link_text">了解更多</span></a></div></div></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="ACRB9HG"><div data-content-type="html" data-appearance="default" data-element="main"><div class="membership-items">
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_footer_content-production',
                    'preview_image' => '.template-managertwlaneigefootercontentproduction65801ee851706.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .jcb-icon{
                      width: 35px;
                      margin-left: 5px;
                    }
                    </style>
                    <div class="footer_top_nav">
                      <div class="footer_nav">
                        <div class="nav_items">
                          <div class="nav_item box_link">
                            <h5>推薦系列</h5>
                            <ul>
                              <li><a href="{{store direct_url=\'korean-style-maintenance.html\'}}">韓式保養</a></li>
                              <li><a href="{{store direct_url=\'make-up.html\'}}">韓系彩妝</a></li>
                              <li><a href="{{store direct_url=\'best-seller.html\'}}">明星商品</a></li>
                              <li>
                                <a href="{{store direct_url=\'member-benefits.html\'}}"
                                  >蘭芝會員福利</a>
                              </li>
                              <!--<li>
                                <a href="{{store direct_url=\'sales/order/history\'}}">訂單/退款</a>
                              </li>-->
                            </ul>
                          </div>
                          <div class="nav_item box_link">
                            <h5>常見問題</h5>
                            <ul>
                              <li><a style="text-transform: uppercase;" href="{{store direct_url=\'faq\'}}">FAQ</a></li>
                              <li><a style="text-transform: uppercase;" href="/sales/guest/form">訂單進度查詢</a></li>
                              <li><a style="text-transform: uppercase;" href="{{store direct_url=\'ugc-terms-and-conditions\'}}">UGC 服務條款</a></li>
                              <li><a style="text-transform: uppercase;" href="/contact/">線上客服信箱</a></li>
                            </ul>
                          </div>
                          <div class="nav_item">
                            <h5>聯繫我們</h5>
                            <ul>
                              <li>
                                <span>客服免付費電話</span>
                                <span>0800-600-308 (手機可撥打)</span> 
                              </li>
                              <!--<li>
                                <span>電郵</span>
                                <a href="mailto:sulwhasoo@amorepacific.com.hk"
                                  >sulwhasoo@amorepacific.com.hk</a
                                >
                              </li>-->
                              <li>
                                <span>客服服務時間</span>
                                週一至週五9:00-18:00(國定例假日除外)
                              </li>
                            </ul>
                          </div>
                        </div>
                      </div>
                    </div>
                    <div class="footer_card">
                      <ul>
                        <li>
                          <img src="{{media url=wysiwyg/Tw/card2.png}}" alt="Card Pay 2" />
                        </li>
                        <li>
                          <img src="{{media url=wysiwyg/Tw/card3.png}}" alt="Card Pay 3" />
                        </li>
                        <li>
                          <img class="jcb-icon" src="{{media url=wysiwyg/wysiwyg/JCB_logo.png}}" alt="Card Pay 1" />
                        </li>
                      </ul>
                    </div>
                    <div class="footer_bottom">
                      <div class="footer_links">
                        <ul>
                          <li><a href="{{store direct_url=\'sitemap\'}}">網站地圖</a></li>
                          <li>
                            <a href="{{store direct_url=\'stores/info/list\'}}">尋找門市</a>
                          </li>
                          <li>
                            <a href="{{store direct_url=\'privacy-policy\'}}">隱私權政策</a>
                          </li>
                          <li>
                            <a href="{{store direct_url=\'terms-and-conditions\'}}">服務條款</a
                            >
                          </li>
                          <!--<li><a href="{{store direct_url=\'membership-terms-and-conditions\'}}">2022會員計劃條款及細則</a></li>
                          <li><a href="https://www.sulwhasoo.com/hk/en/index.html" target="_blank">前往品牌網頁 <span class="icon"></span></a></li>-->
                        </ul>
                      </div>
                      <div class="social_links">
                        <ul>
                          <li>
                            <a target="_blank" href="https://www.instagram.com/laneigetw/"
                              ><span class="instagram"></span
                            ></a>
                          </li>
                          <li>
                            <a target="_blank" href="https://www.facebook.com/laneigetw"
                              ><span class="facebook"></span
                            ></a>
                          </li>
                          <li>
                            <a target="_blank" href="https://www.youtube.com/user/LaneigeTaiwan"
                              ><span class="ytb"></span
                            ></a>
                          </li>
                    -     <li>
                            <a target="_blank" href="https://page.line.me/kzz8080v"
                              ><span class="line"></span
                            ></a>
                          </li>
                          <!--<li>
                            <a target="_blank" href="https://us.weibo.com/index"
                              ><span class="weibo"></span
                            ></a>
                          </li>
                          <li>
                            <a target="_blank" href="https://www.sulwhasoo.com/hk/zh/misc/sulwhasoo-mobile-app/sulwhasoo-mobile-app.html"><span class="mobile"></span></a>
                          </li>-->
                        </ul>
                      </div>
                    </div>
                    
                    <style>
                    .footer_top .footer_top_nav .nav_items .nav_item li a {
                      text-transform : lowercase;
                    }
                    </style></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw-laneige-footer-social-links-production',
                    'preview_image' => '.template-managertwlaneigefootersociallinksproduction65801f2357e79.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .footer-store-info p {color: #767676; font-size: 11px;}
                    @media (max-width: 767px) {
                    .footer-store-info {border-bottom: 1px solid #eee !important; margin-bottom: 0 !important; padding: 10px 0 !important; text-align: center;}
                    }
                    </style></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_cookie_policy_content-production',
                    'preview_image' => '.template-managertwlaneigecookiepolicycontentproduction65801fb4e4610.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><div class="content_policy">
                    <p style="text-align:center;">我們使用 cookie 來改善您的網站體驗。 <br />
                    繼續瀏覽我們的網站，即表示您同意使用 cookie。
                    <a href="{{store direct_url=\'privacy-policy\'}}" target="_blank">點擊此處</a>查看我們的 Cookie 政策。</p>
                    <button class="action primary">同意</button>
                </div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_popup_cookie_content-production',
                    'preview_image' => '.template-managertwlaneigepopupcookiecontentproduction6580207c946e7.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><div class="content_cookie">
                    <div class="bg_popup">
                <img src="{{media url=wysiwyg/wysiwyg/20231212-_BN_720x960_.jpg}}" alt="" />
                    </div>
                    <div class="content">
                        <!--<p class="title">新年限定禮遇​</p>-->
                        <!-- <p class="title">​</p>
                        <p style="padding: 0 10px;color: white">1月27日至31日期間於網店購買正價產品滿HK$2,000​
                                即享HK$500折扣。【優惠碼:HBD500】</p>-->
                         <!-- <p style="padding: 0 10px;color: white">
                                Bloom of Love 買1送2<br>
                                2月1-14日網店獨家增量快閃
                        </p>
                        <p class="title">套裝即享$100折扣​</p>
                        <p style="padding: 0 10px">
                           2月15-28日限時禮遇
                        </p>
                        <p>優惠碼: FEB100</p>
                        -->
                        <!--<p class="title">3月23-31增量快閃​​​​</p>
                        <p style="padding: 0 10px">人參精華買50ml送24ml​<br>美白安瓶買20g送10g</p>
                        <p style="font-size: 12px; padding: 0 10px; padding-top: 15px; color: white">須登入網店，每人限享乙次。<br />不可與其他折扣優惠或優惠碼同時使用。​</p>
                        <p style="font-size: 12px; padding: 0 10px; padding-top: 15px;">*每人限享乙次，必須登入網店，不可與其他優惠碼或折扣禮遇同時使用。</p>-->
                        <!--<p style="font-size: 12px; padding: 0 10px; padding-top: 15px; color: white">*以上禮遇每人限享乙次，須登入網店</p>-->
                        <div class="action">
                            <a class="btn btn_white secondary" href="{{store direct_url=\'value-set.html\'}}">立即選購</a>
                        </div>
                    </div>
                </div>
                
                <style>
                .content_cookie .content .action{
                   margin: 20px 0 0;
                }
                 .content_cookie .content {
                    bottom: 20px;
                 }
                
                .content_cookie .content .btn_white {
                  color: white;
                  border-color: white;
                }
                .content_cookie .content .btn_white:hover {
                  color: #222;
                  background-color: white;
                }
                .content_cookie .content>p
                {
                  color: white;
                }
                .content_cookie .content .title{
                  font-size: 24px;
                }
                @media (max-width: 767px) {
                 .modal-popup .modal-inner-wrap.modal_cookie {
                    min-height: 510px !important;
                    width: 350px;
                 }
                 .content_cookie .content .action{
                    margin: 15px 0 0;
                 }
                 .content_cookie .content {
                    bottom: 17px;
                 }
                 .content_cookie .content .title{
                   font-size: 18px;
                 }
                }
                </style></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_block_menu_mobile-production',
                    'preview_image' => '.template-managertwblockmenumobileproduction658020d78b953.jpg',
                    'template' => '<style>#html-body [data-pb-style=AI07GM1],#html-body [data-pb-style=OM3QUPJ]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=A96UXF8],#html-body [data-pb-style=DXW1CQR]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=BUXVOR0],#html-body [data-pb-style=I4A2DOI]{display:flex;width:100%}#html-body [data-pb-style=A7SEVD3],#html-body [data-pb-style=DOUMS1G],#html-body [data-pb-style=OYPMJB8],#html-body [data-pb-style=XBLSLDE]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:50%;align-self:stretch}#html-body [data-pb-style=QSB57UC]{border-style:none}#html-body [data-pb-style=VFNEX43],#html-body [data-pb-style=XR92L64]{max-width:100%;height:auto}#html-body [data-pb-style=SBPPAIS]{border-style:none}#html-body [data-pb-style=DQ0RNBV],#html-body [data-pb-style=RKRJMUB]{max-width:100%;height:auto}#html-body [data-pb-style=CPGVD08],#html-body [data-pb-style=DFB7KXI],#html-body [data-pb-style=NVLI01I],#html-body [data-pb-style=RS6NNJ7]{display:none}#html-body [data-pb-style=H0NF6AY]{display:inline-block}#html-body [data-pb-style=UURIX4D]{text-align:center}#html-body [data-pb-style=HL486P7]{display:inline-block}#html-body [data-pb-style=Y7TXPNK]{text-align:center}@media only screen and (max-width: 768px) { #html-body [data-pb-style=QSB57UC],#html-body [data-pb-style=SBPPAIS]{border-style:none} }</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="blog-menu-mobile" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="AI07GM1"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="DXW1CQR"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="BUXVOR0"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="OYPMJB8"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="QSB57UC"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D004_A_1.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="VFNEX43"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D004_A_1.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="XR92L64"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="A7SEVD3"><h4 data-content-type="heading" data-appearance="default" data-element="main">完美新生三效賦活精華</h4><div data-content-type="text" data-appearance="default" data-element="main" data-pb-style="RS6NNJ7"><p id="HOE2D9W">3種精華集成一瓶，快速、準確、強效</p></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="CPGVD08"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="H0NF6AY"><div class="pagebuilder-button-primary" data-element="empty_link" data-pb-style="UURIX4D"><span data-element="link_text">了解更多</span></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-viewmore" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="TXW3EEA"><a class="pagebuilder-button-secondary" href="water-bank-blue-hyaluronic-cream-50ml-for-dry-skin.html" target="" data-link-type="default" data-element="link" data-pb-style="E5UVN1P"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div class="blog-menu-mobile" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="OM3QUPJ"><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="A96UXF8"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="I4A2DOI"><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="XBLSLDE"><figure data-content-type="image" data-appearance="full-width" data-element="main" data-pb-style="SBPPAIS"><img class="pagebuilder-mobile-hidden" src="{{media url=wysiwyg/AP-TW-D004_B_1.jpg}}" alt="" title="" data-element="desktop_image" data-pb-style="DQ0RNBV"><img class="pagebuilder-mobile-only" src="{{media url=wysiwyg/AP-TW-D004_B_1.jpg}}" alt="" title="" data-element="mobile_image" data-pb-style="RKRJMUB"></figure></div><div class="pagebuilder-column" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="DOUMS1G"><h4 data-content-type="heading" data-appearance="default" data-element="main">水酷修護保濕精華</h4><div data-content-type="text" data-appearance="default" data-element="main" data-pb-style="NVLI01I"><p id="BMDT223">立即提升肌膚六倍保水力！速效補水，一整天水潤有感。</p></div><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main" data-pb-style="DFB7KXI"><div data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="HL486P7"><div class="pagebuilder-button-primary" data-element="empty_link" data-pb-style="Y7TXPNK"><span data-element="link_text">了解更多</span></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><div data-content-type="buttons" data-appearance="inline" data-same-width="false" data-element="main"><div class="btn-viewmore" data-content-type="button-item" data-appearance="default" data-element="main" data-pb-style="TXW3EEA"><a class="pagebuilder-button-secondary" href="water-bank-blue-hyaluronic-cream-50ml-for-dry-skin.html" target="" data-link-type="default" data-element="link" data-pb-style="E5UVN1P"><span data-element="link_text">了解更多</span></a></div></div></div></div></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
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
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_cart_product_bottom-production',
                    'preview_image' => '.template-managertwcartproductbottomproduction6580216bf32d5.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .cart-discount .product-item-info{
                    display: flex;
                    padding: 16px;
                    margin-bottom: 0 !important;
                    }
                    .cart-discount .product-item-photo{
                      width: 28%;
                    }
                    .cart-discount .product-item-details{
                      width: 50%;
                      padding: 5px !important;
                    text-align: left;
                    align-items: flex-start !important;
                    display: block !important;
                    }
                    .cart-discount .slick-slide{
                      width: 50% !important;
                    background: #d2d7f91a;
                    }
                    .cart-discount .slick-track{
                      opacity: 1;
                        width: 100% !important;
                        transform: translate3d(0px, 0px, 0px);
                        display: flex;
                    }
                    .cart-discount .block-static-block{
                      width: 100%;
                      padding: 0;
                    }
                    .cart-discount .product-reviews-summary{
                      display: none !important;
                    }
                    .cart-discount .actions-secondary{
                        display: none !important;
                    }
                    .cart-discount .product-image-container{
                    padding: 0px !important;
                    background: none !important;
                    }
                    .cart-discount .tocart{
                    background: none !important;
                        border: 0px solid #333 !important;
                        margin-top: 0px !important;
                        padding: 0 !important;
                        color: #333 !important;
                    }
                    .cart-discount .product_tags{
                    display: none !important;
                    }
                    .cart-discount .tocart::after{
                        content: \'\e905\';
                        font-family: \'icons\';
                        font-size: 11px;
                        margin-left: 2px;
                    }
                    .cart-discount .block{
                    border: 0px solid #333 !important;
                    padding: 0px !important;
                    }
                    .cart-discount .ex-title{
                    text-align: left;
                        font-size: 20px;
                        font-weight: bolder;
                    }
                    .cart-discount .tocart:hover{
                    background: none !important;
                    }
                    .cart-discount .giftset-contents-slider{
                    margin-top: 18px !important;
                    }
                    .cart-discount .product-item-name{
                        margin: -9px 0 0 !important;
                    }
                    .cart-discount .product-item-link{
                    font-weight: bold;
                    }
                    .cart-discount .product-item .price-box {
                        margin: 0px 0 6px !important;
                    }
                    @media only screen and (max-width: 768px){
                    .cart-discount .giftset-contents-slider {
                        padding: 0 !important;
                    }
                    .cart-discount .tocart span{
                    color: #333 !important;
                    font-size: 12px !important;
                    }
                    .cart-discount{
                        width: 100% !important;
                    }
                    .cart-discount .block{
                    padding: 0 !important;
                    }
                    .main-recommendation-slider {
                        margin-left: 0px !important;
                    }
                    .cart-discount .product-item-info{
                    padding: 5px !important;
                    }
                    }
                    .cart-discount .tocart:hover {
                        background: none !important;
                    }
                    .cart-discount .tocart:active {
                        background: none !important;
                    }
                    .products-grid .product-item .product-item-details .actions-primary .tocart:hover {
                        background: none !important;
                        background-color: #ffffff !important;
                        border-color: #ffffff !important;
                    }
                    .products-grid .product-item .product-item-details .actions-primary .tocart:focus {
                    background: none !important;
                        background-color: #fff !important;
                    }
                    </style></div><div data-content-type="html" data-appearance="default" data-element="main"><div class="giftset_home_content giftset_tab_content">
                        <div class="giftset_tab main-recommendation-slider">
                            <div class="product data items" data-mage-init=\'{"tabs":{"openedState":"active"}}\'>
                               <h2 class="ex-title">專屬推薦</h2>
                    <hr/>
                                <div class="data item content giftset-contents-slider">
                    {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" show_pager="0" products_count="10" progress="false" mobile="1.5" desktop="4" template="product/widget/content/grid-slide.phtml" cache_lifetime="0" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`any`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_111`^],`1--2`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`operator`:`==`,`value`:`simple_222`^]^]"}}
                    
                                </div>
                    
                            </div>
                        </div>
                    </div>
                    <script type="text/javascript">
                            require([\'jquery\', \'slick\'], function($, slick) {
                                $(document).ready(function() {
                                    $(\'.giftset-contents-slider\').each(function(index, element) {
                                        var $prdRecomm =  $(this).find(\'.product-items\'),
                                            $prdRecommProgressBar = $(this).find(\'.progress .progress_ing\'),
                                            $prdRecommSlidesToShow = 4;
                                        
                                        $prdRecomm.on(\'init\', function(event, slick){
                                            (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.56;
                                            var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow));
                    
                                            $prdRecommProgressBar.css({
                                                \'width\': calc + \'%\',
                                            });
                                        });
                    
                                        $prdRecomm.slick({
                                            dots:false,
                                            slidesToShow: $prdRecommSlidesToShow,
                                            slidesToScroll: $prdRecommSlidesToShow,
                                            infinite: false,
                                            responsive: [
                                                {
                                                    breakpoint: 768,
                                                    settings: {
                                                        dots: true,
                                                        autoplay: true,
                                                        slidesToShow: 1.56,
                                                        slidesToScroll: 1,
                                                        prevArrow: false,
                                                        nextArrow: false
                                                    }
                                                },
                                            ]
                                        }).on(\'beforeChange\', function(event, slick, currentSlide, nextSlide){
                                            (window.innerWidth > 768) ? $prdRecommSlidesToShow = 4 : $prdRecommSlidesToShow = 1.56;
                                            var calc = (100 / (slick.slideCount / $prdRecommSlidesToShow)) * ((nextSlide / $prdRecommSlidesToShow) + 1);
                    
                                            $prdRecommProgressBar.css({
                                                \'width\': calc + \'%\',
                                            });
                                        });
                                    });
                                })
                            });
                        </script>
                    <style>
                    .main-recommendation-slider {text-align: center;}
                    .main-recommendation-slider .tab-contents {position: relative;}
                    .main-recommendation-slider .tab-titles {margin: 0 auto 40px !important;text-align: left;border-bottom: 1px solid #ddd;width: auto;display: inline-block;}
                    
                    .main-recommendation-slider .product.data.items .item.title {width: auto;float: none;margin-bottom: 0;display: inline-block;}
                    .main-recommendation-slider .product.data.items .item.title .data.switch {padding: 0 24px;text-transform: capitalize;color: #969696;font-size: 18px;line-height: 42px;height: 42px;border-bottom: 2px solid #fff;display: inline-block;}
                    .main-recommendation-slider .product.data.items .item.title.active .data.switch {color: #222;font-weight: 500;border-bottom-color: #222;}
                    .main-recommendation-slider .tab-contents .data.content {display: block !important;opacity: 0;visibility: hidden;height: 0;}
                    .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {visibility: visible;opacity: 1;height: auto;}
                    .giftset_home_content .block.widget.block-products-slider .progress, .giftset_home_content .block.widget.block-products-slider .slick-pause {
                      display: none;
                    }
                    @media(min-width: 768px){
                        .main-recommendation-slider .block.widget.block-products-slider .progress {display: none;}
                    }
                    @media(max-width: 767px){
                        .main-recommendation-slider {margin-left: 20px;}
                        .main-recommendation-slider .tab-titles {border: 0;margin: 0 auto 15px !important;width: 767px;white-space: nowrap;}
                        .main-recommendation-slider .product.data.items .item.title .data.switch {font-size: 14px;line-height: 30px;height: 30px;}
                        .main-recommendation-slider .product.data.items {margin: 0;}
                        .main-recommendation-slider .product.data.items .item.title {margin: 0 16px 0 0;}
                        .main-recommendation-slider .product.data.items .item.title .data.switch {padding: 0;}
                        .main-recommendation-slider .block.widget.block-products-slider .products.product-items {margin: 0;}
                        .main-recommendation-slider .product-image-container {width: 100% !important;}
                        .main-recommendation-slider .block.widget.block-products-slider .products.product-items .product-item {padding: 0 6px;}
                        .main-recommendation-slider .block.widget.block-products-slider .slick-track {margin: 0 -6px;}
                        .main-recommendation-slider .block.widget.block-products-slider .products-grid {padding-bottom: 24px;}
                        .main-recommendation-slider .block.widget.block-products-slider .progress {width: 110px;left: calc(50% - 15px);margin-right: 30px;}
                        .tab-head_titles {overflow-x: auto;}
                        .main-recommendation-slider .product.data.items .item.content {border: 0;}
                        .main-recommendation-slider .block.widget.block-products-slider .slick-dots {display: none !important;}
                        .main-recommendation-slider .block.widget.block-products-slider .slick-pause {position: absolute;bottom: -38px;top: initial;right: calc(50% - 70px);}
                        .main-recommendation-slider .block.widget.block-products-slider .slick-pause::after {font-size: 10px;line-height: 20px;content: "\e812";font-family: \'fontello\';color: #222;}
                        .main-recommendation-slider .block.widget.block-products-slider .slick-pause.active::after {content: "\e813";}
                        .giftset-contents-slider .block.widget.block-products-slider .products.product-items .slick-list {margin: 0;}
                    }
                    </style></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_login_coupon-production',
                    'preview_image' => '.template-managertwlaneigelogincouponproduction658022154aede.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .login-container .block-static-block{
                    width: calc(50% - 10px);
                    }
                    .login-container .image-container{
                    width: calc(50% - 10px);
                    }
                    .login-container .image-coupon{
                    position: relative;
                        height: 850px;
                        width: 100%;
                    }
                    .login-container .image-backgroud{
                    position: absolute;
                        width: 680px;
                        height: 850px;
                    }
                    @media only screen and (max-width: 1199px){
                    .login-container .image-coupon{
                    display: none !important;
                    }
                    }
                    </style></div><div data-content-type="html" data-appearance="default" data-element="main"><div class="image-coupon">
                        <a href="https://mcproduction.tw.laneige.com/member-benefits.html">
                             <img src="{{media url=wysiwyg/Tw/AP-TW-D002-D.jpg}}" alt="" />
                        </a>
                    </div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_login_benefit-production',
                    'preview_image' => '.template-managertwlaneigeloginbenefitproduction6580228485362.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .block-customer-login{
                    padding-top: 9px !important;
                    }
                    .login-container .login-benifit{
                        display: flex;
                        min-height: 144px;
                        padding: 24px 48px;
                        background-color: #ecf5fa;
                        column-gap: 30px;
                        justify-content: space-between;
                    }
                    .login-container .login-benifit .block-login-benifit-right {
                        display: flex;
                    }
                    @media (min-width: 1200px){
                    .login-container .block-customer-login .block-content {
                        height: 634px;
                    }
                    }
                    
                    .image-coupon img{
                        height: 850px;
                        max-width: 100%;
                    }
                    </style></div><div class="login-benifit" data-content-type="html" data-appearance="default" data-element="main"><div class="block-login-benifit-left">
                        <div class="title">
                            蘭芝官網禮遇
                        </div>
                        <div class="content">
                           登入官網會員，享有獨家優惠與禮遇
                        </div>
                    </div>
                    <div class="block-login-benifit-right">
                        <div class="block">
                            <div class="image">
                    <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D002_A_56x56px_.png}}" alt="" />
                            </div>
                            <div class="content">
                               每筆消費皆可累點
                            </div>
                        </div>
                        <div class="block">
                            <div class="image">
                    <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D002_B_56x56px_.png}}" alt="" />
                            </div>
                            <div class="content">
                               當月壽星享生日禮
                            </div>
                        </div>
                      <div class="block">
                            <div class="image">
                                <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D002_C_56x56px_.png}}" alt="" />
                            </div>
                            <div class="content">
                               獨家贈品與優惠組
                            </div>
                        </div>
                    </div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_laneige_categories_mobile_block-production',
                    'preview_image' => '.template-managertwlaneigecategoriesmobileblockproduction6580230ded8b3.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><div class="categories_slide_wrapprer">
                    <div id="categories_slide">
                        <div class="categories_item"><a href="{{store direct_url=\'get-to-know-laneige.html\'}}">了解蘭芝</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'korean-style-maintenance.html\'}}">韓系保養</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'make-up.html\'}}">韓系彩妝</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'latest-news.html\'}}">最新消息</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'best-new.html\'}}">優惠專區</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'new.html\'}}">最新商品</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'best-seller.html\'}}">熱賣商品</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'stores/info/list\'}}">尋找門市</a></div>
                        <div class="categories_item"><a href="{{store direct_url=\'member-benefits.html\'}}">會員計畫</a></div>
                    </div>
                  </div>
                  <script type="text/javascript">
                      require([\'jquery\',\'slick\'],function($,slick){
                          $(document).ready(function() {
                              $(\'#categories_slide\').slick({
                                  dots: false,
                                  arrows: false,
                                  infinite: false,
                                  speed: 300,
                                  slidesToShow: 1,
                                  slidesToScroll: 1,
                                  variableWidth: true
                              });
                  
                          });
                  
                      });
                  </script>
                  <style>
                  #categories_slide .categories_item {display: none;}
                  @media(min-width: 768px){
                  .categories_slide_wrapprer {
                      display: none;
                  }
                  }
                  @media(max-width: 767px){
                  .categories_slide_wrapprer {
                      padding: 0 0 0 20px;
                  }
                  .categories_slide_wrapprer a {
                      font-size: 14px;
                      line-height: 18.5px;
                      padding: 13px 35px 11px 0;
                      display: block;
                      text-transform: uppercase;
                      color: #222222;
                      font-weight: 500;
                  }
                  }
                  </style></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'sign_up_laneige_shop_benefits-production',
                    'preview_image' => '.template-managersignuplaneigeshopbenefitsproduction658023898416b.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><div class="benefits-box">
                    <div class="benefits-title">
                    <h3>優惠不錯過</h3>
                    <p>加入蘭芝官網會員</p>
                    </div>
                    <div class="benefits-items">
                        <div class="benefits-item">
                            <img style="border-radius:50%; width: 80px; height: 80px; max-width: 80px" src="{{media url=wysiwyg/Tw/AP-TW-D006_A_120x120px_.png}}" alt="" />
                            <p>每筆消費皆可累點​</p>
                        </div>
                        <div class="benefits-item">
                            <img style="border-radius:50%; width: 80px; height: 80px; max-width: 80px" src="{{media url=wysiwyg/Tw/AP-TW-D006_B_120x120px_.png}}" alt="" />
                            <p>當月壽星享生日禮</p>
                        </div>
                        <div class="benefits-item">
                            <img style="border-radius:50%; width: 80px; height: 80px; max-width: 80px" src="{{media url=wysiwyg/Tw/AP-TW-D006_C_120x120px_.png}}" alt="" />
                            <p>獨家贈品與優惠組​</p>
                        </div>
                    </div>
                    </div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-top-banner-runner-production',
                    'preview_image' => '.template-managerhktopbannerrunnerproduction6580245f7cc17.jpg',
                    'template' => '<style>#html-body [data-pb-style=KMQKEWT],#html-body [data-pb-style=W8W0HS2]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}#html-body [data-pb-style=KMQKEWT]{border-style:none;border-width:1px;border-radius:0;margin:0;padding:10px}#html-body [data-pb-style=D8QJ8AH]{border-style:none;border-width:1px;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=AQU7T89]{background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;align-self:stretch}#html-body [data-pb-style=LB5SL3G]{display:flex;width:100%}#html-body [data-pb-style=LNHATOB]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll;width:100%;align-self:stretch}#html-body [data-pb-style=T2OHX1S]{text-align:center}#html-body [data-pb-style=O1I7U7N]{text-align:center;border-style:none;border-width:1px;border-radius:0;margin:0;padding:0}#html-body [data-pb-style=G6A4D70]{margin:0}#html-body [data-pb-style=DNYV1TU]{background-position:left top;background-size:cover;background-repeat:no-repeat;border-style:none;border-width:1px;border-radius:0}#html-body [data-pb-style=FD1PCGB]{padding:0;background-color:transparent}#html-body [data-pb-style=QDAAQFO]{margin:0}#html-body [data-pb-style=MB2I5XQ]{background-position:left top;background-size:cover;background-repeat:no-repeat;border-style:none;border-width:1px;border-radius:0}#html-body [data-pb-style=RI1B4I5]{padding:0;background-color:transparent}</style><div class="laneige-top-banner hideslide hideslide-display-none" data-content-type="row" data-appearance="full-bleed" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="main" data-pb-style="KMQKEWT"><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="D8QJ8AH"><style>
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
</style></div><div class="pagebuilder-column-group" data-background-images="{}" data-content-type="column-group" data-appearance="default" data-grid-size="12" data-element="main" data-pb-style="AQU7T89"><div class="pagebuilder-column-line" data-content-type="column-line" data-element="main" data-pb-style="LB5SL3G"><div class="pagebuilder-column content_top_banner" data-content-type="column" data-appearance="full-height" data-background-images="{}" data-element="main" data-pb-style="LNHATOB"><div class="pagebuilder-slider" data-content-type="slider" data-appearance="default" data-autoplay="true" data-autoplay-speed="6000" data-fade="true" data-infinite-loop="true" data-show-arrows="false" data-show-dots="false" data-element="main" data-pb-style="O1I7U7N"><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main" data-pb-style="G6A4D70"><a href="https://tw.laneige.com/best-new/vbom.html" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="DNYV1TU"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="" title="" data-element="overlay" data-pb-style="FD1PCGB"><div class="pagebuilder-poster-content"><div data-element="content"><p data-syno-attrs="{"textMarks":[{"_":"font_size","value":"18pt"},{"_":"font_family","value":"Calibri"},{"_":"color","value":"#000000"}]}">全館結帳滿$1000免運費</p></div></div></div></div></a></div><div data-content-type="slide" data-slide-name="" data-appearance="poster" data-show-button="never" data-show-overlay="never" data-element="main" data-pb-style="QDAAQFO"><a href="https://tw.laneige.com/events/detail/index/id/109" target="_blank" data-link-type="default" title="" data-element="link"><div class="pagebuilder-slide-wrapper" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="wrapper" data-pb-style="MB2I5XQ"><div class="pagebuilder-overlay pagebuilder-poster-overlay" data-overlay-color="" aria-label="" title="" data-element="overlay" data-pb-style="RI1B4I5"><div class="pagebuilder-poster-content"><div data-element="content"><p data-syno-attrs="{"textMarks":[{"_":"font_size","value":"18pt"},{"_":"font_family","value":"Calibri"},{"_":"color","value":"#000000"}]}">結帳用LINE Pay綁定中信LINE Pay卡享6%回饋</p></div></div></div></div></a></div></div><div data-content-type="html" data-appearance="default" data-element="main" data-pb-style="T2OHX1S"><button class="btn-close-top hideslide" id=\'satpHideShowjs\' type="button"><span>Close</span></button></div></div></div></div><div data-content-type="html" data-appearance="default" data-element="main"><script>
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
</script></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="W8W0HS2"><div data-content-type="html" data-appearance="default" data-element="main"><style>
#ui-id-3 {color:#fa860b !important;}
</style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk-laneige-new-latest-recommendations-production',
                    'preview_image' => '.template-managerhklaneigenewlatestrecommendationsproduction6580278f037f5.jpg',
                    'template' => '<style>#html-body [data-pb-style=I66XTHD]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="I66XTHD"><div data-content-type="html" data-appearance="default" data-element="main"><div class="bestseller_content">
                    <div class="container">
                        <div class="cms_title">
                            <h1>新產品</h1>
                        </div>
                        <div class="banner_new banner_category">
                 <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D012-1-2_A_PC_2070x690_.jpg}}" alt="" class="img-desktop" />
                <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D012-1-2_A_MO_720-x-720_.jpg}}" alt="" class="img-mb"/>
                           <div class="banner-cont-wrap">
                            <h1 class="tit">NEO型塑霧感氣墊 </h1>
                            <p class="desc">&ZeroWidthSpace;蘭芝首創！NEO無痕貼膚科技，光NEO妝容。</p>
                               <div class="btn-link-wrap">
                                 <a class="btn-link" href="/skincare/line/water-bank.html" ap-click-area="Product" ap-click-data="PLP">了解更多</a>
                                </div>
                            </div>
                         </div>
                        <div class="bestseller_tab main-recommendation-slider">
                          <div class="product data items" data-mage-init=\'{"tabs":{"openedState":"active"}}\'>
                            <!-- <div class="tab-titles">
                              <div class="data item title" data-role="collapsible" id="tab-label-all">
                                <a class="data switch"
                                   tabindex="-1"
                                   data-toggle="trigger"
                                   href="#bestseller_tab_all"
                                   id="tab-label-bestseller_tab_all-title">
                                    全部
                                </a>
                              </div>
                            </div> -->
                            <div class="tab-contents">
                              <div style="opacity: 1; visibility: visible; height: auto;" class="data item content"
                                 aria-labelledby="tab-label-bestseller_tab_all-title" id="bestseller_tab_all" data-role="content">
                {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" id="bestseller_tab_all_widget" show_pager="0" products_count="15" template="Sapt_CustomWidget::widget/bestseller-page.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`1485`^]^]" page_var_name="pmqouj"}}
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
                <style>
                    .catalog-category-view .page-wrapper .page-header {
                        order: -5;
                    }
                    .catalog-category-view .page-wrapper .sections.nav-sections {
                        order: -4;
                    }
                    .catalog-category-view .page-wrapper .breadcrumbs {
                        order: -3;
                    }
                    .catalog-category-view .page-wrapper .category-view {
                        order: -2;
                    }
                  .catalog-category-view .satp-megamenu.navigation {
                  border-bottom: 1px solid #ddd;
                }
                .bestseller_tab.main-recommendation-slider {
                  overflow: hidden;
                }
                  .bestseller-slider-home h2 {
                  margin-bottom: 10px;
                }
                  .main-recommendation-slider {
                  text-align: left;
                }
                .main-recommendation-slider .tab-contents {
                  position: relative;
                }
                .main-recommendation-slider .tab-titles {
                  margin: 0 auto 40px !important;
                  text-align: left;
                  border-bottom: 1px solid #ddd;
                  width: auto;
                  display: block;
                }
                
                .main-recommendation-slider .product.data.items .item.title {
                  width: auto;
                  float: none;
                  display: inline-block;
                }
                .main-recommendation-slider .product.data.items .item.title .data.switch {
                  padding: 0 24px;
                text-transform: capitalize;
                color: #969696;
                font-size: 18px;
                line-height: 42px;
                height: 42px;
                border-bottom: 2px solid #fff;
                display: inline-block;
                }
                .main-recommendation-slider .product.data.items .item.title.active .data.switch {
                  color: #222;
                  font-weight: 700;
                  border-bottom-color: #222;
                }
                .main-recommendation-slider .tab-contents .data.content {
                  display: block !important;
                  opacity: 0;
                  visibility: hidden;
                  height: 0;
                }
                .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {
                  visibility: visible;
                  opacity: 1;
                  height: auto;
                }
                </style></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .sidebar-main {
                       display: none !important;
                    }
                    .product.data.items .giftblock-contents-slider.item.content {
                        border: none !important;
                    }
                
                    .main-recommendation-slider {
                        text-align: center;
                    }
                
                    .main-recommendation-slider .tab-contents {
                        position: relative;
                    }
                
                    .main-recommendation-slider .tab-titles {
                        margin: 0 auto 40px !important;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                        width: auto;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title {
                        width: auto;
                        float: none;
                        margin-bottom: 0;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title .data.switch {
                        padding: 0 24px;
                        text-transform: capitalize;
                        color: #969696;
                        font-size: 18px;
                        line-height: 42px;
                        height: 42px;
                        border-bottom: 3px solid #fff;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title.active .data.switch {
                        color: #222;
                        font-weight: 700;
                        border-bottom-color: #222;
                    }
                
                    .main-recommendation-slider .tab-contents .data.content {
                        display: block !important;
                        opacity: 0;
                        visibility: hidden;
                        height: 0;
                    }
                
                    .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {
                        visibility: visible;
                        opacity: 1;
                        height: auto;
                    }
                
                    @media (max-width: 767px) {
                        .bestseller_home_content .main-recommendation-slider {
                            margin: 0;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-head_titles {
                            margin-left: 20px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-contents {
                            margin: 0 20px;
                            overflow: hidden;
                            padding: 0 10px 2px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-contents .progress {
                            bottom: 3px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .slick-track {
                            margin: 0;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .products.product-items .product-item {
                            padding: 0;
                        }
                    }
                
                    .product-items .action.towishlist:not(.updated):before,
                    .products.list.items .action.towishlist:not(.updated):before {
                        padding: 10px 10px 20px 20px;
                    }
                
                    .products.list.items .action.towishlist {
                        top: 0;
                        right: 0;
                    }
                
                    @media (max-width: 767px) {
                
                        .products .products.list.items .action.towishlist,
                        .products.list.items .action.towishlist {
                            top: 0px;
                            right: 0;
                        }
                
                        .bestseller_home_content .tab-head_titles {
                            overflow: hidden;
                        }
                
                        .product.data.items .giftblock-contents-slider.item.content {
                            border: none !important;
                        }
                
                        .bestseller_tab.main-recommendation-slider .tab-titles {
                            max-width: 100%;
                        }
                    }
                .bestseller_content .img-mb{
                display: none;
                }
                @media (max-width: 767px){
                .bestseller_content .banner_category img.img-desktop {
                    display: none;
                }
                .bestseller_content .banner_category img.img-mb {
                    display: block;
                }
                }
                .bestseller_content .btn-link-wrap{
                display: none;
                margin-top: 30px;
                }
                </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'laneige-best-product-production',
                    'preview_image' => '.template-managerlaneigebestproductproduction658027cbd63cf.jpg',
                    'template' => '<style>#html-body [data-pb-style=PGPGFWH]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="PGPGFWH"><div data-content-type="html" data-appearance="default" data-element="main"><div class="bestseller_content">
                    <div class="container">
                        <div class="cms_title">
                            <h1>新產品</h1>
                        </div>
                        <div class="bestseller_tab main-recommendation-slider">
                          <div class="product data items" data-mage-init=\'{"tabs":{"openedState":"active"}}\'>
                            <!-- <div class="tab-titles">
                              <div class="data item title" data-role="collapsible" id="tab-label-all">
                                <a class="data switch"
                                   tabindex="-1"
                                   data-toggle="trigger"
                                   href="#bestseller_tab_all"
                                   id="tab-label-bestseller_tab_all-title">
                                    全部
                                </a>
                              </div>
                            </div> -->
                            <div class="tab-contents">
                              <div style="opacity: 1; visibility: visible; height: auto;" class="data item content"
                                 aria-labelledby="tab-label-bestseller_tab_all-title" id="bestseller_tab_all" data-role="content">
                {{widget type="Magento\CatalogWidget\Block\Product\ProductsList" id="bestseller_tab_all_widget" show_pager="0" products_count="15" template="Sapt_CustomWidget::widget/bestseller-page.phtml" conditions_encoded="^[`1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`aggregator`:`all`,`value`:`1`,`new_child`:``^],`1--1`:^[`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`category_ids`,`operator`:`==`,`value`:`1485`^]^]" page_var_name="pmqouj"}}
                              </div>
                            </div>
                          </div>
                        </div>
                    </div>
                </div>
                <style>
                    .catalog-category-view .page-wrapper .page-header {
                        order: -5;
                    }
                    .catalog-category-view .page-wrapper .sections.nav-sections {
                        order: -4;
                    }
                    .catalog-category-view .page-wrapper .breadcrumbs {
                        order: -3;
                    }
                    .catalog-category-view .page-wrapper .category-view {
                        order: -2;
                    }
                  .catalog-category-view .satp-megamenu.navigation {
                  border-bottom: 1px solid #ddd;
                }
                .bestseller_tab.main-recommendation-slider {
                  overflow: hidden;
                }
                  .bestseller-slider-home h2 {
                  margin-bottom: 10px;
                }
                  .main-recommendation-slider {
                  text-align: left;
                }
                .main-recommendation-slider .tab-contents {
                  position: relative;
                }
                .main-recommendation-slider .tab-titles {
                  margin: 0 auto 40px !important;
                  text-align: left;
                  border-bottom: 1px solid #ddd;
                  width: auto;
                  display: block;
                }
                
                .main-recommendation-slider .product.data.items .item.title {
                  width: auto;
                  float: none;
                  display: inline-block;
                }
                .main-recommendation-slider .product.data.items .item.title .data.switch {
                  padding: 0 24px;
                text-transform: capitalize;
                color: #969696;
                font-size: 18px;
                line-height: 42px;
                height: 42px;
                border-bottom: 2px solid #fff;
                display: inline-block;
                }
                .main-recommendation-slider .product.data.items .item.title.active .data.switch {
                  color: #222;
                  font-weight: 700;
                  border-bottom-color: #222;
                }
                .main-recommendation-slider .tab-contents .data.content {
                  display: block !important;
                  opacity: 0;
                  visibility: hidden;
                  height: 0;
                }
                .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {
                  visibility: visible;
                  opacity: 1;
                  height: auto;
                }
                </style></div><div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .sidebar-main {
                       display: none !important;
                    }
                    .product.data.items .giftblock-contents-slider.item.content {
                        border: none !important;
                    }
                
                    .main-recommendation-slider {
                        text-align: center;
                    }
                
                    .main-recommendation-slider .tab-contents {
                        position: relative;
                    }
                
                    .main-recommendation-slider .tab-titles {
                        margin: 0 auto 40px !important;
                        text-align: left;
                        border-bottom: 1px solid #ddd;
                        width: auto;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title {
                        width: auto;
                        float: none;
                        margin-bottom: 0;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title .data.switch {
                        padding: 0 24px;
                        text-transform: capitalize;
                        color: #969696;
                        font-size: 18px;
                        line-height: 42px;
                        height: 42px;
                        border-bottom: 3px solid #fff;
                        display: inline-block;
                    }
                
                    .main-recommendation-slider .product.data.items .item.title.active .data.switch {
                        color: #222;
                        font-weight: 700;
                        border-bottom-color: #222;
                    }
                
                    .main-recommendation-slider .tab-contents .data.content {
                        display: block !important;
                        opacity: 0;
                        visibility: hidden;
                        height: 0;
                    }
                
                    .main-recommendation-slider .tab-contents .data.content[aria-hidden="false"] {
                        visibility: visible;
                        opacity: 1;
                        height: auto;
                    }
                
                    @media (max-width: 767px) {
                        .bestseller_home_content .main-recommendation-slider {
                            margin: 0;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-head_titles {
                            margin-left: 20px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-contents {
                            margin: 0 20px;
                            overflow: hidden;
                            padding: 0 10px 2px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .tab-contents .progress {
                            bottom: 3px;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .slick-track {
                            margin: 0;
                        }
                
                        .bestseller_home_content .main-recommendation-slider .block.widget.block-products-slider .products.product-items .product-item {
                            padding: 0;
                        }
                    }
                
                    .product-items .action.towishlist:not(.updated):before,
                    .products.list.items .action.towishlist:not(.updated):before {
                        padding: 10px 10px 20px 20px;
                    }
                
                    .products.list.items .action.towishlist {
                        top: 0;
                        right: 0;
                    }
                
                    @media (max-width: 767px) {
                
                        .products .products.list.items .action.towishlist,
                        .products.list.items .action.towishlist {
                            top: 0px;
                            right: 0;
                        }
                
                        .bestseller_home_content .tab-head_titles {
                            overflow: hidden;
                        }
                
                        .product.data.items .giftblock-contents-slider.item.content {
                            border: none !important;
                        }
                
                        .bestseller_tab.main-recommendation-slider .tab-titles {
                            max-width: 100%;
                        }
                    }
                .bestseller_content .img-mb{
                display: none;
                }
                @media (max-width: 767px){
                .bestseller_content .banner_category img.img-desktop {
                    display: none;
                }
                .bestseller_content .banner_category img.img-mb {
                    display: block;
                }
                }
                .bestseller_content .btn-link-wrap{
                display: none;
                margin-top: 30px;
                }
                </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'tw_account_dashboard_banner-production',
                    'preview_image' => '.template-managertwaccountdashboardbannerproduction65811c22331aa.jpg',
                    'template' => '<div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .line-add-friend-bottom{
                    display: none;
                    }
                    .line-add-friend-bottom .link-text{
                    margin-left: 15px;
                    }
                    .line-add-friend-bottom .note{
                    font-size: 16px;
                        color: #ADADAD;
                        margin-bottom: 10px;
                    }
                    .line-add-friend-bottom .link{
                    font-size: 16px;
                        color: #333;
                        font-weight: bold;
                    }
                    .line-add-friend-bottom img{
                        width: 80px;
                        margin-top: 8px;
                    }
                    .line-add-friend-bottom .link::after {
                        width: 17px;
                        height: 17px;
                        display: inline-block;
                        content: \'\';
                        background-image: url(data:image/svg+xml;base64,PHN2ZyBkYXRhLW5hbWU9IjIwcHhfQ2F0ZWdvcnlfJmd0OyIgeG1sbnM9Imh0dHA6Ly93d3cudzMub3JnLzIwMDAvc3ZnIiB3aWR0aD0iMjAiIGhlaWdodD0iMjAiIHZpZXdCb3g9IjAgMCAyMCAyMCI+CiAgICA8cGF0aCBkYXRhLW5hbWU9IlBhdGggODQ1MjU3IiBkPSJNNiAwIDAgNmw2IDYiIHRyYW5zZm9ybT0icm90YXRlKDE4MCA2Ljc1IDguMjUpIiBzdHlsZT0ic3Ryb2tlOiMyMjI7c3Ryb2tlLWxpbmVjYXA6cm91bmQ7c3Ryb2tlLXdpZHRoOjEuNXB4O2ZpbGw6bm9uZTtzdHJva2UtbWl0ZXJsaW1pdDoxMCIvPgo8L3N2Zz4K);
                        background-size: 100%;
                        background-repeat: no-repeat;
                        background-position: center;
                        vertical-align: text-bottom;
                    }
                    @media(max-width: 767px){
                    .line-add-friend{
                    display: none !important;
                    }
                    .line-add-friend-bottom{
                    display: flex;
                        justify-content: center;
                    }
                    }
                    </style>
                    <div class="line-add-friend-bottom">
                    <a href="https://lin.ee/qH9tM5h" target="_blank">
                                    <img src="{{media url=wysiwyg/banner/LINE_Brand_icon_2_1.png}}" alt="" />
                    </a>
                    <div class="link-text">
                    <p class="note">Get First-Hand Beauty with Laneige Line Official</p>
                    <a class="link" href="https://lin.ee/qH9tM5h">Add Line Friend</a>
                    </div>
                    </div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk_laneize_banner_b_type-production',
                    'preview_image' => '.template-managerhklaneizebannerbtypeproduction65811f4b13f16.jpg',
                    'template' => '<style>#html-body [data-pb-style=NFNUJ31]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="NFNUJ31"><div data-content-type="html" data-appearance="default" data-element="main"><div class="banner_category">
                    <img src="{{media url=wysiwyg/Tw/AP-TW-D009_B_PC_2880x510_.jpg}}" class="hidden-pc" alt="護膚"  />
                    <img src="{{media url=wysiwyg/Tw/AP-TW-D009_B_MO_720x360_.jpg}}"  class="hidden-mo" alt="護膚" />
                    <div class="banner-cont-wrap">
                             <h1 class="tit">NEO型塑霧感氣墊 </h1>
                             <p class="desc">​蘭芝首創！NEO無痕貼膚科技，50小時無瑕控油，更輕盈服貼不沾染。獨家FAM緊緻透亮科技，一拍如薄荷般清爽的微霧光NEO妝容。</p>
                             <div class="btn-link-wrap">
                               <a class="btn-link" href="/skincare/line/water-bank.html" ap-click-area="Product" ap-click-name="Click - Banner" ap-click-data="PLP Banner">了解更多</a>
                             </div>
                    </div>
                 </div>
                 
                 
                 <style>
                 .banner_category img {
                     height:340px;
                 }
                 
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                     top: 50%;
                     transform: translate(-50%, -50%);
                 padding: 0px 109px 0;
                 height: fit-content;
                 }
                 @media (min-width: 767px) {
                    .hidden-mo {
                       display:none;
                    }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                 max-width:600px;
                 line-height:50px;
                 }
                 }
                 @media (max-width: 767px) {
                    .hidden-pc {
                       display:none;
                    }
                    .banner_category {
                       height:360px;
                    }
                 }
                 
                 @media (min-width: 360px) and (max-width:767px) {
                    .banner_category img {
                     height:auto;
                 }
                 .banner_category {
                 height:auto;
                 }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                     top: 50%;
                     transform: translate(-50%, -50%);
                 padding: 0px 64px 0;
                 }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                  max-width:280px;
                 }
                 
                 }
                 @media (min-width: 767px) and (max-width:1280px) {
                    .hidden-pc {
                       display:block;
                    }
                    .hidden-mo {
                       display:none;
                    }
                 .banner_category img {
                 object-position: 65%;
                 }
                 
                 }
                 @media (max-width: 360px) {
                    .banner_category {
                       height:180px;
                 
                    }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                     top: 50%;
                     transform: translate(-50%, -50%);
                     padding: 0px 32px 0;
                 }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                  max-width:140px;
                 }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .btn-link {
                 max-width:93px;
                 }
                 }
                 @media (min-width: 361px) and (max-width:480px) {
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                  max-width:140px;
                 }
                   .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                  padding:0 32px 0;
                 }
                 .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .btn-link {
                 max-width:93px;
                 }
                 }
                 </style></div></div></div>',
                    'created_for' => 'any'
               ],
               [
                    'name' => 'hk_laneize_banner_b_type_style-production',
                    'preview_image' => '.template-managerhklaneizebannerbtypestyleproduction65811f99cbefc.jpg',
                    'template' => '<style>#html-body [data-pb-style=VPOK1CX]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="VPOK1CX"><div data-content-type="html" data-appearance="default" data-element="main"><div class="banner_category">
                    <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D009_A_PC_2880x510_.jpg}}" class="hidden-pc" alt="保養" />
                    <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D009_A_MO_720x360_.jpg}}" class="hidden-mo" alt="保養" />
                      
                       <div class="banner-cont-wrap">
                                <h1 class="tit">保養 </h1>
                                <p class="desc">完美新生全系列升級！蘊含抗老界最強科技成分，針對臉部3大老化指標：光澤、彈性、撫紋，從根本解決肌膚老化困擾，完美還原無瑕光透彈潤肌</p>
                                <div class="btn-link-wrap">
                                  <a class="btn-link" href="/skincare/line/water-bank.html" ap-click-area="Product" ap-click-name="Click - Banner" ap-click-data="PLP Banner">了解更多</a>
                                </div>
                       </div>
                    </div>
                    
                    
                    <style>
                    .banner_category img {
                        height:340px;
                    }
                    
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                        top: 50%;
                        transform: translate(-50%, -50%);
                    padding: 0px 109px 0;
                    height: fit-content;
                    }
                    @media (min-width: 767px) {
                       .hidden-mo {
                          display:none;
                       }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                    max-width:600px;
                    line-height:50px;
                    }
                    }
                    @media (max-width: 767px) {
                       .hidden-pc {
                          display:none;
                       }
                       .banner_category {
                          height:360px;
                       }
                    }
                    
                    @media (min-width: 360px) and (max-width:767px) {
                       .banner_category img {
                        height:auto;
                    }
                    .banner_category {
                    height:auto;
                    }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                        top: 50%;
                        transform: translate(-50%, -50%);
                    padding: 0px 64px 0;
                    }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                     max-width:280px;
                    }
                    
                    }
                    @media (min-width: 767px) and (max-width:1280px) {
                       .hidden-pc {
                          display:block;
                       }
                       .hidden-mo {
                          display:none;
                       }
                    .banner_category img {
                    object-position: 65%;
                    }
                    
                    }
                    @media (max-width: 360px) {
                       .banner_category {
                          height:180px;
                    
                       }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                        top: 50%;
                        transform: translate(-50%, -50%);
                        padding: 0px 32px 0;
                    }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                     max-width:140px;
                    }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .btn-link {
                    max-width:93px;
                    }
                    }
                    @media (min-width: 361px) and (max-width:480px) {
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .tit {
                     max-width:140px;
                    }
                      .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap {
                     padding:0 32px 0;
                    }
                    .catalog-category-view .banner_category:not(.banner_new) .banner-cont-wrap .btn-link {
                    max-width:93px;
                    }
                    }
                    </style></div></div></div>',
                    'created_for' => 'any'
                  ],
                  [
                    'name' => 'tw_laneige_ordersuccess_favorites-production',
                    'preview_image' => '.template-managertwlaneigeordersuccessfavoritesproduction658283253f266.jpg',
                    'template' => '<style>#html-body [data-pb-style=Y6T5CX8]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="customer_favories" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="Y6T5CX8"><div data-content-type="html" data-appearance="default" data-element="main"><div class="block_title">
                    <h2>Customer Favorites</h2>
                    <p>Perfect combination with</p>
                    </div></div><div data-content-type="products" data-appearance="carousel" data-autoplay="false" data-autoplay-speed="4000" data-infinite-loop="false" data-show-arrows="true" data-show-dots="false" data-carousel-mode="default" data-center-padding="90px" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="Magento_PageBuilder::catalog/product/widget/content/carousel.phtml" anchor_text="" id_path="" show_pager="0" products_count="10" condition_option="sku" condition_option_value="111975616, 111975618, LANEIGE-NEO-Foundation-Glow, LANEIGE-NEO-Foundation-Matte,NEO-Cushion-Glow,LANEIGE-Light-Fit-Pact, LANEIGE-Light-Fit-Powder, LANEIGE-Water-glow-Base-Corrector, LANEIGE-NEO-Cushion-Matte" type_name="Catalog Products Carousel" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`111975616, 111975618, LANEIGE-NEO-Foundation-Glow, LANEIGE-NEO-Foundation-Matte,NEO-Cushion-Glow,LANEIGE-Light-Fit-Pact, LANEIGE-Light-Fit-Powder, LANEIGE-Water-glow-Base-Corrector, LANEIGE-NEO-Cushion-Matte`^]^]" sort_order="date_newest_top"}}</div><div data-content-type="html" data-appearance="default" data-element="main"><style>
                    .checkout-onepage-success .customer_favories .slick-slider .slick-arrow {
                        background-color: rgba(255, 255, 255, 0.7) !important;
                        background-image: none !important;
                    }
                    .checkout-onepage-success .customer_favories .slick-slider .slick-arrow.slick-next {
                        background-image: none;
                        top: 50%;
                        transform: translate(0, -100px);
                        right: 10px;
                        width: 30px;
                        height: 30px;
                        color: #666;
                    }
                    .checkout-onepage-success .customer_favories .slick-slider .slick-arrow:before {
                        content: "\e905";
                        font-size: 16px;
                        position: absolute;
                        top: 0;
                        right: 0;
                        top: 50%;
                        right: 50%;
                        color: #000;
                        transform: translate(50%, -50%);
                        padding-left: 0;
                    }
                    .checkout-onepage-success .customer_favories .slick-slider .slick-arrow.slick-prev {
                        width: 30px;
                        height: 30px;
                        transform: translate(0, -100px);
                        top: 50%;
                        left: 10px;
                    }
                    
                    .checkout-onepage-success .customer_favories .slick-slider .slick-arrow.slick-prev:before {
                        content: "\e909";
                        padding-right: 0;
                    }
                    </style></div></div></div>',
                    'created_for' => 'any'
                  ],
                  [
                    'name' => 'tw_laneige_order_failed_content-production',
                    'preview_image' => '.template-managertwlaneigeorderfailedcontentproduction658283c4e9996.jpg',
                    'template' => '<style>#html-body [data-pb-style=PREXSDC],#html-body [data-pb-style=V5LX3JP]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="PREXSDC"><div data-content-type="html" data-appearance="default" data-element="main"><div class="service_center">
                    <h2 class="title">Service Center</h2>
                    <p><strong>Call</strong> <a href="tel:+852 2895 6008">+852 2895 6008</a> (Ofiice hour : 10:00 ~ 19:00)</p>
                    <p><strong>Email</strong> <a href="mailto:laneige@amorepacific.com.hk">laneige@amorepacific.com.hk</a></p>
                    <p><strong>Go to</strong> <a class="btn_inquiry" href="#">1:1 inquiry</a></p>
                </div></div></div></div><div data-content-type="row" data-appearance="contained" data-element="main"><div class="customer_favories" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="V5LX3JP"><div data-content-type="html" data-appearance="default" data-element="main"><div class="block_title">
                <h2>Customer Favorites</h2>
                <p>Perfect combination with</p>
                </div></div><div data-content-type="products" data-appearance="carousel" data-autoplay="false" data-autoplay-speed="4000" data-infinite-loop="false" data-show-arrows="true" data-show-dots="false" data-carousel-mode="default" data-center-padding="90px" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="Magento_PageBuilder::catalog/product/widget/content/carousel.phtml" anchor_text="" id_path="" show_pager="0" products_count="10" condition_option="sku" condition_option_value="111975616, 111975618, LANEIGE-NEO-Foundation-Glow, LANEIGE-NEO-Foundation-Matte,NEO-Cushion-Glow,LANEIGE-Light-Fit-Pact, LANEIGE-Light-Fit-Powder, LANEIGE-Water-glow-Base-Corrector, LANEIGE-NEO-Cushion-Matte" type_name="Catalog Products Carousel" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`111975616, 111975618, LANEIGE-NEO-Foundation-Glow, LANEIGE-NEO-Foundation-Matte,NEO-Cushion-Glow,LANEIGE-Light-Fit-Pact, LANEIGE-Light-Fit-Powder, LANEIGE-Water-glow-Base-Corrector, LANEIGE-NEO-Cushion-Matte`^]^]" sort_order="date_newest_top"}}</div></div></div>',
                    'created_for' => 'any'
                  ]
          ];
          foreach ($sampleData as $data) {
               $this->templateFactory->create()->setData($data)->save();
          }
     }

     public static function getDependencies()
     {
          return [];
     }

     public function getAliases()
     {
          return [];
     }

}
