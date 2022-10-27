import React, {useEffect, useRef} from 'react';
import './_ShowcaseCarousel.scss';
import useWindowDimensions from '../../../Hooks/useWindowDimensions';
import {carouselShowcase} from '../../../data/carousel';
import {preventDefault, wheelOpt, wheelEvent, preventDefaultForScrollKeys} from '../../../libs/events';

import gsap from 'gsap';
import { SplitText } from 'gsap/SplitText';
import { ScrollTrigger } from "gsap/ScrollTrigger";

export type ShowcaseCarouselProps = {}

function ShowcaseCarousel({}: ShowcaseCarouselProps) {

    gsap.registerPlugin(ScrollTrigger);

    const carousel = useRef<HTMLElement>();

    const portfolioShowcase = useRef<HTMLElement>();

    const projectWrapper = useRef<HTMLElement>();

    const projectTitle = useRef<HTMLElement>();

    const carouselHeadline = useRef<HTMLElement>();

    const carouselHeadText = useRef<HTMLElement>(null);

    const showcase = useRef<HTMLElement[]>([]);

    const showcaseTitle = useRef<HTMLElement[]>([]);

    const showcaseImage = useRef<HTMLImageElement[]>([]);

    const { height, width } = useWindowDimensions();

    const wrapFirstTrans = width / 100 * 90;

    // call this to Disable
    const disableScroll = () => {
        window.addEventListener('DOMMouseScroll', preventDefault, false); // older FF
        window.addEventListener(wheelEvent, preventDefault, wheelOpt); // modern desktop
        window.addEventListener('touchmove', preventDefault, wheelOpt); // mobile
        window.addEventListener('keydown', preventDefaultForScrollKeys, false);
    }

    // call this to Enable
    const enableScroll = () => {
        window.removeEventListener('DOMMouseScroll', preventDefault, false);
        window.removeEventListener(wheelEvent, preventDefault, wheelOpt);
        window.removeEventListener('touchmove', preventDefault, wheelOpt);
        window.removeEventListener('keydown', preventDefaultForScrollKeys, false);
    }

    const showcaseOpenings = () => {
        const showcaseCheck = portfolioShowcase;

        const carousel = gsap.timeline({
            onStart: function () {
                disableScroll();
            },
            onComplete: function () {
                enableScroll();
            }
        })

        const wrapper = projectWrapper
        // const wrapFirstTrans = width / 100 * 90;
        // const wrapWidth = width


        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {

            // all our animations can use selector text like ".box"
            // and it's properly scoped to our component
                carousel.fromTo('.cas-line span', 1, {
                    y: '100%'
                }, {
                    y: '0%',
                    stagger: 0.1,
                    ease: 'power3.out'
                }, 2)

                // @ts-ignore
                carousel.fromTo('.cas-project-wrapper', 2.5, {
                    x: width
                }, {
                    x: wrapFirstTrans,
                    ease: 'circ.inOut',
                }, .2)

                carousel.fromTo('.cas-bg-text', 1.5, {
                    x: '-100%'
                }, {
                    x: '100%',
                    ease: 'power2.out',
                }, .7)

                carousel.fromTo('.cas-progress', 1.5, {
                    width: '0%'
                }, {
                    width: '50%',
                    ease: 'power2.out',
                }, 2.2)

                carousel.fromTo('.showcase-footer', 1, {
                    opacity: 0,
                }, {
                    opacity: 1
                }, 2.7)

        },
            // comp // <- IMPORTANT! Scopes selector text
            );


    }

    const aliothShowcaseCarousel = () => {
       /* var project = $('.cas-project'),
            wrapper = $('.cas-project-wrapper'),
            projectTitles = $('.cas-titles'),
            headline = $('.cas-headline'),
            bgText = $('.cas-bg-text'),
            wrapFirstTrans = $(window).outerWidth() / 100 * 90,
            activeProject;*/

        const project = showcase;
        const wrapper = projectWrapper;
        const projectTitles = projectTitle;
        const headline = carouselHeadline;
        const bgText = carouselHeadText;
        const wrapFirstTrans = width / 100 * 90;
        // const activeProject;

        // @ts-ignore
        new SplitText(headline.current, {
            type: 'lines',
            linesClass: 'cas-line',
        })

        project.current.forEach((element, i) => {
            const $this = null;
            const title = showcaseTitle;
            const image = showcaseImage;

            // console.log('showcaseImage', image.current[i].alt);
            // console.log('showcaseImage', image.current[i].src);

            // $this.addClass('cas_project_' + i)
            showcase.current[i].classList.add('cas_project_' + i);

            // $this.attr('data-title', '.title_' + i)
            // showcase.current[i].setAttribute('data-title', '.title_' + i)

            // projectTitles.append(title.addClass('title_' + i));

            // title.attr('data-project', '.cas_project_' + i)

            i++;
        });

        // console.log('ProjectTitles', projectTitles)

       /*

        projectTitles.wrapInner('<div class="cas-titles-wrap"></div>')

        $('.cas-line').wrapInner('<span></span>')

        */


        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {

            // all our animations can use selector text like ".box"
            // and it's properly scoped to our component
            // gsap.to('.cas-line span', {
            gsap.to('.cas-line', {
                y: '-100%',
                stagger: 0.01,
                ease: 'none',
                scrollTrigger: {
                    trigger: '.carousel-showcase',
                    start: 'top top',
                    end: '10% top',
                    scrub: 1,
                    markers: false
                }
            })

            gsap.fromTo(bgText.current, {
                x: '100%'
            }, {
                x: '-30%',
                scrollTrigger: {
                    trigger: '.carousel-showcase',
                    scrub: 1,
                    start: 'top top',
                    end: 'bottom+=3000 top',
                    markers: false

                }
            });

        }); // <- IMPORTANT! Scopes selector text




       const totProj = showcase.current.length;
       const transVal = totProj * 250 - 250;

        /*

        var mobileQuery = window.matchMedia('(max-width: 450px)'),
            tabletQuery = window.matchMedia('(min-width: 450px) and (max-width: 900px)');


        // Check if the media query is true
        if (mobileQuery.matches) {

            transVal = totProj * 80 - 80
        }

        if (tabletQuery.matches) {

            transVal = totProj * 200 - 200
        }*/


        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {

            // gsap.to('.cas-titles-wrap', {
            gsap.to('.cas-titles', {
                y: -transVal,
                scrollTrigger: {
                    trigger: '.carousel-showcase',
                    scrub: 1,
                    start: 'top top',
                    end: 'bottom+=3000 top',
                    markers: false
                }
            });

            gsap.set('.showcase-footer', {
                position: 'fixed'
            });

            let csw = gsap.fromTo(wrapper.current, {
                x: wrapFirstTrans
            }, {
                // x: '-' + (wrapper.current.outerWidth() - $(window).outerWidth() + 350)

            });

            // windowWidth = $(window).outerWidth(),

            let windowWidth = width;

            let css = new ScrollTrigger({
                trigger: '.carousel-showcase',
                animation: csw,
                pin: true,
                scrub: 1,
                id: 'showcaseScroll',
                start: 'top top',
                end: 'bottom+=3000 top',
                markers: false,
                onUpdate: function (self:any) {

                    // let prog = $('.cas-progress span');

                    // gsap.to(prog, {
                    //     width: self.progress * 100 + '%'
                    // })

                    project.current.forEach((item, idx) => {
                        // let $this = $(this)
                        // console.log('onUpdate', 'project', 'item', item)
                    })

                },
                onLeave: function () {
                    gsap.to('.showcase-footer', {
                        opacity: 0
                    })
                },
                onEnterBack: function () {
                    gsap.to('.showcase-footer', {
                        opacity: 1
                    })



                },
            });

        }); // <- IMPORTANT! Scopes selector text


        /*$('.cs-title').on('mouseenter', function () {
            let $this = $(this);
            $this.addClass('active')
        })*/

        /*$('.cs-title').on('mouseleave', function () {

            let $this = $(this);

            $this.removeClass('active')
        })*/
    }

    const welcomeAnimation = () => {

        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {
            // all our animations can use selector text like ".box"
            // and it's properly scoped to our component
            const CarouselWelcome = gsap.timeline({
                onStart: function () {
                    disableScroll();
                },
                onComplete: function () {

                    enableScroll();
                }

            });
                // wrapper = $('.cas-project-wrapper'),
                // wrapFirstTrans = $(window).outerWidth() / 100 * 90,
                // wrapWidth = -wrapper.outerWidth();

            CarouselWelcome.fromTo('.cas-line span', 1, {
                y: '100%'
            }, {
                y: '0%',
                stagger: 0.1,
                ease: 'power3.out'
            }, 2)


            CarouselWelcome.fromTo('.cas-project-wrapper', 2.5, {
                x: wrapWidth
            }, {
                x: wrapFirstTrans,
                ease: 'circ.inOut',
            }, .2)

            CarouselWelcome.fromTo('.cas-bg-text', 1.5, {
                x: '-100%'
            }, {
                x: '100%',
                ease: 'power2.out',
            }, .7)

            CarouselWelcome.fromTo('.cas-progress', 1.5, {
                width: '0%'
            }, {
                width: '50%',
                ease: 'power2.out',
            }, 2.2)

            CarouselWelcome.fromTo('.showcase-footer', 1, {
                opacity: 0,
            }, {
                opacity: 1
            }, 2.7)


        }); // <- IMPORTANT! Scopes selector text
    }

    useEffect(() => {
        // aliothShowcaseCarousel();
        // showcaseOpenings();

    }, [])
    return (
        <div className="portfolio-showcase carousel-showcase"
             data-barba-namespace="sc-carousel" ref={portfolioShowcase}>
            <div className="cas-bg-text" ref={carouselHeadText}>Featured Works</div>
            <div className="cas-headline" ref={carouselHeadline}>
                Hello! We are Digital Agecy,<br/>a digital creative agency from London.<br/>Creating wonderful digital products<br/>and a pinch of rock'n roll!
            </div>
            <div className="cas-project-wrapper" ref={projectWrapper}>

                {carouselShowcase.map((slide, idx) => (
                    // @ts-ignore
                    <div className="cas-project"
                         key={slide.name + '_' + idx}
                         ref={(el) => (showcase.current[idx] = el)}>
                        <div className="cs-image">
                            <img
                                alt={slide.name}
                                src={slide.image}
                                ref={(el) => (showcaseImage.current[idx] = el)}/>
                        </div>
                        <div className="cs-title"
                             ref={(el) => (showcaseTitle.current[idx] = el)}>
                            <a href={slide.link}>{slide.name}</a>
                        </div>
                    </div>
                ))}
            </div>

            <div className="cas-titles" ref={projectTitle}></div>

            <div className="cas-progress"><span></span></div>

            <div className="showcase-footer">
                <div className="showcase-footer-left">
                    <div className="scroll-notice" data-target="#secondSec">
                        <span className="sn_bef"></span>
                        <span>SCROLL</span>
                    </div>
                </div>
                <div className="showcase-footer-right">
                    <div className="a-plus-button"><a href="works.html">
                        <span><span>ALL PROJECTS</span></span>
                    </a>
                    </div>
                </div>

            </div>

        </div>
    );
}

export default ShowcaseCarousel
