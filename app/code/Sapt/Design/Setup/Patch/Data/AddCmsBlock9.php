<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock9 implements DataPatchInterface
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
            'title' => 'Laneige New Product Latest Recommendations',
            'identifier' => 'tw-laneige-new-latest-recommendations',
            'content' => '<style>#html-body [data-pb-style=YCRSQ5A]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div class="margin140" data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="YCRSQ5A"><div data-content-type="html" data-appearance="default" data-element="main"><div class="bestseller_content">
            <div class="container">
                <div class="cms_title">
                    <h1>新產品</h1>
                </div>
                <div class="banner_new banner_category">
         <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D012-1-2_A_PC_2070x690_.jpg}}" alt="" class="img-desktop" />
        <img src="{{media url=wysiwyg/wysiwyg/AP-TW-D012-1-2_A_MO_720-x-720_.jpg}}" alt="" class="img-mb"/>
                   <div class="banner-cont-wrap">
                    <h1 class="tit">全新</h1>
                    <p class="desc">NEO型塑氣墊EX</p>
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