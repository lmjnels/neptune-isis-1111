import React, {useRef} from 'react';
import {CarouselSlide} from "../../../data/carousel";

export type FullscreenHeroProps = {
    slides: CarouselSlide[];
}

export default function FullscreenHero({}: FullscreenHeroProps) {

    const fsSlider = useRef(null);

    function aliothShowcaseFullscrenSlider() {

        var project = $('.fs-project'),
            fsImages = $('.fs-images'),
            fract = $('.fs-fraction'),
            currentSlide,
            nextSlide,
            prevSlide,
            titLines,
            mets,
            but,
            fsTit,
            actIndex,
            actImg,
            nextImg,
            prevImg,
            activeProj;

        project.each(function (i) {

            i++

            let $this = $(this),
                image = $this.find('.fs-project-image'),
                title = $this.find('.fs-title'),
                meta = $this.find('.fs-meta'),
                button = $this.find('.fs-button');

            $this.attr('data-title', title.text())

            const titleSplit = new SplitText(title, {
                type: 'lines, chars',
                linesClass: 'fs-tit-line',
                charsClass: 'fs-tit-char'
            })

            meta.wrapInner('<span></span>')
            button.wrapInner('<span></span>')


            image.attr('data-project', '.project_' + i);
            image.attr('data-index', i);
            $this.addClass('project_' + i)

            $('.fs-images').append(image.addClass('swiper-slide'));

        });


        $('.fs-images .swiper-slide').wrapInner('<div class="fs-img-wrap"></div>')

        $('.fs-tit-char').wrapInner('<span></span>')

        $('.fs-images').wrapInner('<div class="swiper-wrapper"></div>')

        var interleaveOffset = 0.5;

        var fsSlider = new Swiper('.fs-images', {
            mousewheel: {
                invert: false,
                eventsTarget: '.fullscreen-slider-showcase'
            },
            allowTouchMove: true,
            touchEventsTarget: '.fullscreen-slider-showcase',
            loop: false,
            breakpoints: {
                // when window width is >= 320px
                450: {
                    allowTouchMove: false,
                },

            },
            pagination: {
                el: '.fs-prog',
                type: 'progressbar',

            },

            slidesPerView: 1,
            navigation: {
                nextEl: '.fs-next',
                prevEl: '.fs-prev',
            },
            speed: 1200,
            parallax: true,
            watchSlidesProgress: true,
            on: {
                progress: function () {
                    let swiper = this;
                    for (let i = 0; i < swiper.slides.length; i++) {
                        let slideProgress = swiper.slides[i].progress,
                            innerOffset = swiper.width * interleaveOffset,
                            innerTranslate = slideProgress * innerOffset;

                        swiper.slides[i].querySelector(".slide-bgimg").style.transform =
                            "translateX(" + innerTranslate + "px)";
                    }
                },
                setTransition: function (speed) {
                    let swiper = this;
                    for (let i = 0; i < swiper.slides.length; i++) {
                        swiper.slides[i].style.transition = speed + "ms";
                        swiper.slides[i].querySelector(".slide-bgimg").style.transition =
                            1200 + "ms";
                    }
                },
            }
        });

        function slideCheck() {

            currentSlide = $('.swiper-slide-active');
            nextSlide = $('.swiper-slide-next');
            prevSlide = $('.swiper-slide-prev');

            actImg = $(currentSlide).find('img');
            nextImg = $(nextSlide).find('img');
            prevImg = $(prevSlide).find('img');

            activeProj = $(currentSlide).data('project');
            actIndex = $(currentSlide).data('index');

            titLines = $(activeProj).find('.fs-tit-char > span');
            fsTit = $(activeProj).find('.fs-title');
            mets = $(activeProj).find('.fs-meta > span');
            but = $(activeProj).find('.fs-button > span');


        }

        slideCheck();


        $('.fs-project').removeClass('active')
        $(activeProj).addClass('active');

        fsSlider.on('slideChange', function () {

            slideCheck();



        })

        var mobileQuery = window.matchMedia('(max-width: 450px)')

        fsSlider.on('slideNextTransitionStart', function () {

            // Check if the media query is true
            if (!mobileQuery.matches) {
                gsap.fromTo(actImg, 2, {
                    scale: 1,
                    rotate: 0
                }, {
                    scale: 1.1,
                    rotate: -5
                }, 0)

                gsap.fromTo(nextImg, 2, {
                    scale: 1.2,
                    rotate: 5
                }, {
                    scale: 1,
                    rotate: 0
                }, 0)


            }


            let slideNextOut = gsap.timeline();

            slideNextOut.fromTo(titLines, .6, {
                x: 0,

            }, {
                x: -100,
                stagger: 0.01,
                ease: 'power1.in',

            }, 0)


            slideNextOut.fromTo('.fs-fraction span', .6, {
                x: 0,
                opacity: 1
            }, {
                x: -30,
                opacity: 0,
                ease: 'power2.in',
            }, .6)

            slideNextOut.fromTo(mets, .6, {
                x: 0,
                opacity: 1
            }, {
                x: -30,
                opacity: 0,
                ease: 'power2.in',
            }, .3)



        })

        fsSlider.on('slideNextTransitionEnd', function () {

            slideCheck();

            $('.fs-project').removeClass('active')
            $(activeProj).addClass('active');

            let slideNextIn = gsap.timeline();

            slideNextIn.fromTo(titLines, .5, {
                x: 100,

            }, {
                x: 0,

                stagger: 0.01,
                ease: 'power1.out',

            }, 0)


            slideNextIn.fromTo('.fs-fraction span', .6, {
                x: 30,
                opacity: 0
            }, {
                x: 0,
                opacity: 1,
                ease: 'power1.out',
                onStart: function () {

                    $('.fs-fraction span').html('0' + actIndex)
                }
            }, .3)

            slideNextIn.fromTo(mets, .6, {
                x: 30,
                opacity: 0
            }, {
                x: 0,
                opacity: 1,
                ease: 'power1.out',
            }, .3)


        })

        fsSlider.on('slidePrevTransitionStart', function () {

            // Check if the media query is true
            if (!mobileQuery.matches) {

                gsap.fromTo(actImg, 2, {
                    scale: 1,
                    rotate: 0
                }, {
                    scale: 1.1,
                    rotate: 5
                }, 0)

                gsap.fromTo(prevImg, 2, {
                    scale: 1.2,
                    rotate: -5
                }, {
                    scale: 1,
                    rotate: 0
                }, 0)

            }


            let slidePrevOut = gsap.timeline();

            slidePrevOut.fromTo(titLines, .5, {
                x: 0,

            }, {
                x: 100,

                stagger: -0.01,
                ease: 'power1.in',

            }, 0)


            slidePrevOut.fromTo('.fs-fraction span', .6, {
                x: 0,
                opacity: 1
            }, {
                x: 30,
                opacity: 0,
                ease: 'power1.in',
            }, .6)

            slidePrevOut.fromTo(mets, .6, {
                x: 0,
                opacity: 1
            }, {
                x: 30,
                opacity: 0,
                ease: 'power1.in',
            }, .3)

        })

        fsSlider.on('slidePrevTransitionEnd', function () {

            slideCheck();

            $('.fs-project').removeClass('active')
            $(activeProj).addClass('active');

            let slidePrevIn = gsap.timeline();

            slidePrevIn.fromTo(titLines, .5, {
                x: -100,

            }, {
                x: 0,

                stagger: -0.01,
                ease: 'power1.out',

            }, 0)


            slidePrevIn.fromTo('.fs-fraction span', .6, {
                x: -30,
                opacity: 0
            }, {
                x: 0,
                opacity: 1,
                ease: 'power1.out',
                onStart: function () {

                    $('.fs-fraction span').html('0' + actIndex)
                }
            }, .3)

            slidePrevIn.fromTo(mets, .6, {
                x: -30,
                opacity: 0
            }, {
                x: 0,
                opacity: 1,
                ease: 'power1.out',
            }, .3)



        })


    }

    function showcaseOpenings() {
// Welcome Animation


        let welcomeAnim = gsap.timeline({
                once: true
            }),

            currentSlide = $('.swiper-slide-active'),
            nextSlide = $('.swiper-slide-next'),
            prevSlide = $('.swiper-slide-prev'),

            actImg = $(currentSlide).find('img'),
            nextImg = $(nextSlide).find('img'),
            prevImg = $(prevSlide).find('img'),

            activeProj = $(currentSlide).data('project'),
            actIndex = $(currentSlide).data('index'),

            titLines = $(activeProj).find('.fs-tit-char > span');


        welcomeAnim.fromTo(titLines, 1.5, {
            x: -100,

        }, {
            x: -0,
            stagger: 0.01,
            ease: 'power2.out',

        }, .3)

        welcomeAnim.fromTo('.fs-fraction span', .6, {
            x: -30,
            opacity: 0
        }, {
            x: 0,
            opacity: 1,
            ease: 'power2.out',
        }, 1)

        welcomeAnim.fromTo('.fs-meta > span', 1, {
            x: -30,
            opacity: 0
        }, {
            x: 0,
            opacity: 1,
            ease: 'power2.out',
        }, 1);

        welcomeAnim.fromTo('.fs-button a', 1.5, {
            x: '-100%',
            opacity: 0
        }, {
            x: '0%',
            opacity: 1,
            ease: 'power2.out',
        }, 1.5)

        welcomeAnim.fromTo('.showcase-footer', 1, {
            opacity: 0
        }, {
            opacity: 1,
            ease: 'power2.out',

        }, 1.7)



        // Welcome Animation
    }

    return (
        <div className="section fullscreen">
            <div className="wrapper-full no-gap no-margin">
                <div className="c-col-12 no-gap no-margin">
                    <div className="portfolio-showcase fullscreen-slider-showcase" ref={fsSlider}>
                        <div className="fs-fraction">
                            {/*<span className="fs-tot">01</span>*/}
                            <div className="fs-project">
                                <div className="fs-project-dets">
                                    <div className="fs-title">

                                    </div>
                                    <div className="fs-meta">
                                        <span className="fs-cat"></span>
                                        <span className="fs-year"></span>
                                    </div>
                                    <div className="fs-button">
                                        <span>
                                            <a href=""></a>
                                        </span>
                                    </div>
                                </div>
                            </div>


                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
