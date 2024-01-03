<?php
declare(strict_types=1);

namespace Sapt\Design\Setup\Patch\Data;

use Magento\Framework\Setup\ModuleDataSetupInterface;
use Magento\Framework\Setup\Patch\DataPatchInterface;
use Magento\Cms\Model\BlockFactory;

class AddCmsBlock7 implements DataPatchInterface
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
            'title' => 'Order success Customer Favorites (TW Laneige)',
            'identifier' => 'tw_laneige_ordersuccess_favorites',
            'content' => '<style>#html-body [data-pb-style=NC31U63]{justify-content:flex-start;display:flex;flex-direction:column;background-position:left top;background-size:cover;background-repeat:no-repeat;background-attachment:scroll}</style><div data-content-type="row" data-appearance="contained" data-element="main"><div data-enable-parallax="0" data-parallax-speed="0.5" data-background-images="{}" data-background-type="image" data-video-loop="true" data-video-play-only-visible="true" data-video-lazy-load="true" data-video-fallback-src="" data-element="inner" data-pb-style="NC31U63"><div data-content-type="html" data-appearance="default" data-element="main"><div class="block_title">
            <h2>Customer Favorites</h2>
            <p>Perfect combination with</p>
            </div></div><div data-content-type="products" data-appearance="carousel" data-autoplay="false" data-autoplay-speed="4000" data-infinite-loop="false" data-show-arrows="true" data-show-dots="false" data-carousel-mode="default" data-center-padding="90px" data-element="main">{{widget type="Magento\CatalogWidget\Block\Product\ProductsList" template="Magento_PageBuilder::catalog/product/widget/content/carousel.phtml" anchor_text="" id_path="" show_pager="0" products_count="20" condition_option="sku" condition_option_value="simple_222,simple_111" type_name="Catalog Products Carousel" conditions_encoded="^[`1`:^[`aggregator`:`all`,`new_child`:``,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Combine`,`value`:`1`^],`1--1`:^[`operator`:`()`,`type`:`Magento||CatalogWidget||Model||Rule||Condition||Product`,`attribute`:`sku`,`value`:`simple_222,simple_111`^]^]" sort_order="date_newest_top"}}</div><div data-content-type="html" data-appearance="default" data-element="main"><style>
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