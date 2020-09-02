/**
 * Copyright © Magento, Inc. All rights reserved.
 * See COPYING.txt for license details.
 */

define([
    'jquery',
    'gsap',
    'matchMedia',
    'domReady!'
], function ($, gsap, mediaCheck) {
    'use strict';

    $.widget('mage.listProducts', {
        _create: function () {
            if ($('.catalog-category-view .products-list').offset()) {
                mediaCheck({
                    media: '(min-width: 768px)',
                    entry: function () {
                        $('.product-item').each(function () {
                            let index = $(this).attr('data-index');
                            let nextIndex = parseInt(index) + 1;
                            let height = $(this).outerHeight();

                            if(index%2 === 0) {
                                let nextElement = '.product-item[data-index="' + nextIndex + '"]';
                                let nextHeight = $(nextElement).outerHeight();

                                if(nextHeight !== null) {
                                    if(height < nextHeight) {
                                        height = nextHeight;
                                    }

                                    $(this).css('height',height);
                                    $(nextElement).css('height',height);
                                }
                            }
                        });

                        let tweenAnimation = function () {
                            let u = $(".products.list .product-item-container"),
                                o = $(".list-contents"),
                                q = 0,
                                s = 0.5,
                                p = 0.5,
                                n = Sine.easeOut;

                            TweenMax.set(o, {
                                y: '0%',
                                opacity: 1,
                                cursor: 'pointer'
                            });
                            u.append('<div class="cover"></div>');
                            u.each(function() {
                                let v = $(this),
                                    x = v.find('.cover');
                                v.one('inview', {
                                    offset: 0.9
                                }, function() {
                                    t(v.find('.list-contents'), q);
                                    q++;
                                    setTimeout(function() {
                                        q--
                                    }, 200)
                                });
                                v.on('mouseenter mouseleave', function(z) {
                                    let B = z.pageX,
                                        A = z.pageY,
                                        H = v.offset().left + v.outerWidth() / 2,
                                        C = v.offset().top + v.outerHeight() / 2,
                                        E = Math.abs(H - B),
                                        D = Math.abs(C - A),
                                        G = {},
                                        F = {};
                                    G.ease = n;
                                    if (z.type === 'mouseenter') {
                                        if (E > D) {
                                            G.top = '0%';

                                            if(H > B) {
                                                G.left = '-100%';
                                            }else {
                                                G.left = '100%';
                                            }
                                            F.left = '0%';
                                        } else {
                                            G.left = '0%';

                                            if(C > A) {
                                                G.top = '-100%';
                                            }else {
                                                G.top = '100%';
                                            }
                                            F.top = '0%';
                                        }
                                    } else {
                                        G = {
                                            top: '0%',
                                            left: '0%',
                                            ease: n
                                        };
                                        if (E > D) {
                                            if(H > B) {
                                                F.left = '-100%';
                                            }else {
                                                F.left = '100%';
                                            }
                                        } else {
                                            if(C > A) {
                                                F.top = '-100%';
                                            }else {
                                                F.top = '100%';
                                            }
                                        }
                                    }
                                    TweenMax.fromTo(x, s, G, F, p)
                                })
                            });

                            function t(w, index) {
                                let v = (index === undefined) ? 0 : index;
                                TweenMax.to(w, 0.5, {
                                    y: '0%',
                                    opacity: 1,
                                    ease: n,
                                    delay: v * 0.3
                                })
                            }
                        };

                        tweenAnimation();
                    }
                });
            }
        }
    });

    return $.mage.listProducts;
});
