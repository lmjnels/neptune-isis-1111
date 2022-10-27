import React, {useEffect, useRef} from 'react';
import gsap from 'gsap';
import SplitText from "gsap/SplitText";
import ScrollTrigger from "gsap/ScrollTrigger";
import './_ScrollingCarousel.scss';
import {carouselShowcase} from '../../../data/carousel';
import useWindowDimensions from '../../../Hooks/useWindowDimensions';

export type ShowcaseCarouselProps = {}

function ScrollingCarousel({}: ShowcaseCarouselProps) {

    gsap.registerPlugin(ScrollTrigger);

    const carousel = useRef<HTMLElement>();

    const portfolioShowcase = useRef<HTMLDivElement>(null);

    const projectWrapper = useRef<HTMLDivElement>(null);

    const projectTitle = useRef<HTMLDivElement>(null);

    const carouselHeadline = useRef<HTMLDivElement>(null);

    const carouselHeadText = useRef<HTMLDivElement>(null);

    const showcase = useRef<HTMLElement[]>([]);
    // const showcase = useRef<HTMLDivElement[]>([]);

    const showcaseTitle = useRef<HTMLElement[]>([]);

    const showcaseImage = useRef<HTMLImageElement[]>([]);

    const { height, width } = useWindowDimensions();

    const wrapFirstTrans = width / 100 * 90;

    const showcaseOpenings = () => {
        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {
            //Welcome Animation

            let sCarouselWelcome = gsap.timeline({
                onStart: function () {
                    // disableScroll();
                },
                onComplete: function () {
                    // enableScroll();
                }
            });

            const wrapper = projectWrapper;
            const wrapFirstTrans = width / 100 * 90;
            const wrapWidth = -width;

            sCarouselWelcome.fromTo('.cas-line span', 1,
                {
                y: '100%'
            },
                {
                y: '0%',
                stagger: 0.1,
                ease: 'power3.out'
            }, 2)

            sCarouselWelcome.fromTo(wrapper, 2.5,
                {
                x: wrapWidth
            },
                {
                x: wrapFirstTrans,
                ease: 'circ.inOut',
            }, .2)

            sCarouselWelcome.fromTo('.cas-bg-text', 1.5,
                {
                x: '-100%'
            },
                {
                x: '100%',
                ease: 'power2.out',
            }, .7)

            sCarouselWelcome.fromTo('.cas-progress', 1.5, {
                width: '0%'
            }, {
                width: '50%',
                ease: 'power2.out',
            }, 2.2)

            sCarouselWelcome.fromTo('.showcase-footer', 1, {
                opacity: 0,
            }, {
                opacity: 1
            }, 2.7)

            //Welcome Animation
        }); // <- IMPORTANT! Scopes selector text
    }

    const aliothShowcaseCarousel = () => {

        // const project = $('.cas-project');
        const project = showcase;
        // const wrapper = $('.cas-project-wrapper');
        const wrapper = projectWrapper;
        // const projectTitles = $('.cas-titles');
        const projectTitles = projectTitle;
        // const headline = $('.cas-headline');
        const headline = carouselHeadline;
        // const bgText = $('.cas-bg-text');
        const bgText = carouselHeadText;
        // const wrapFirstTrans = $(window).outerWidth() / 100 * 90;
        const activeProject = null;


        // create our context. This function is invoked immediately and all GSAP animations and ScrollTriggers created during the execution of this function get recorded so we can revert() them later (cleanup)
        gsap.context(() => {
            new SplitText('.cas-bg-text', {
                type: 'lines',
                linesClass: 'cas-line',
            })

            project.current.forEach((element, i) => {

                const $this = null;
                const title = showcaseTitle;
                const img = showcaseImage;

                showcase.current[i].classList.add('cas_project_' + i);

                // $this.attr('data-title', '.title_' + i) // @todo

                // projectTitles.append(title.addClass('title_' + i)); // @todo

                // title.attr('data-project', '.cas_project_' + i) // @todo

                i++;
            });

            // projectTitles.wrapInner('<div class="cas-titles-wrap"></div>') // manuallly done


            // $('.cas-line').wrapInner('<span></span>') // @todo

            gsap.to('.cas-line span', {
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

            // gsap.fromTo(bgText, {
            gsap.fromTo('.cas-bg-text', {
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

            let totProj = showcase.current.length;

            let transVal = totProj * 250 - 250;

            /*
            let mobileQuery = window.matchMedia('(max-width: 450px)');
            let tabletQuery = window.matchMedia('(min-width: 450px) and (max-width: 900px)');

            // Check if the media query is true
            if (mobileQuery.matches) {
                transVal = totProj * 80 - 80
            }

            if (tabletQuery.matches) {
                transVal = totProj * 200 - 200
            }
            */

            gsap.to('.cas-titles-wrap', {
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
            })


            let csw = gsap.fromTo(wrapper, {
                    x: wrapFirstTrans
                },
                {
                    // @ts-ignore
                    x: '-' + (projectWrapper.current.offsetWidth - width + 350)

                });

                const windowWidth = width;

                new ScrollTrigger({
                    trigger: '.carousel-showcase',
                    animation: csw,
                    pin: true,
                    scrub: 1,
                    id: 'showcaseScroll',
                    start: 'top top',
                    end: 'bottom+=3000 top',
                    markers: false,
                    onUpdate: function (self:any) {

                        /*let prog = $('.cas-progress span');

                        gsap.to(prog, {
                            width: self.progress * 100 + '%'
                        })*/

                        // project.each(function () {
                        //     let $this = $(this)
                        // })

                    },

                    onLeave: function () {
                        gsap.to('.showcase-footer', {
                            opacity: 0
                        })

                    },
                    onEnterBack: function () {
                        gsap.to('.showcase-footer', {
                            opacity: 1
                        });
                    },
                });

            // $('.cs-title').on('mouseenter', function () {
            //     let $this = $(this);
            //     $this.addClass('active')
            // })

            // $('.cs-title').on('mouseleave', function () {
            //     let $this = $(this);
            //     $this.removeClass('active')
            // })
        });
    }


    useEffect(() => {
        showcaseOpenings();
        aliothShowcaseCarousel();

    }, []);

    return (
        <div className="portfolio-showcase carousel-showcase"
             data-barba-namespace="sc-carousel"
             ref={portfolioShowcase}>
            <div className="cas-bg-text" ref={carouselHeadText}>Featured Works</div>
            <div className="cas-headline" ref={carouselHeadline}>
                Hello! We are Digital Agency,<br/>a digital creative agency from London.<br/>Creating wonderful digital products<br/>and a pinch of rock'n roll!
            </div>
            <div className="cas-project-wrapper" ref={projectWrapper}>
                {carouselShowcase.map((slide, idx) => (
                    // @ts-ignore
                    <div className="cas-project"
                         key={slide.name + '_' + idx}
                         ref={(el) => (showcase.current[idx] = el as HTMLDivElement)}>
                        <div className="cs-image">
                            <img
                                alt={slide.name}
                                src={slide.image}
                                ref={(el) => (showcaseImage.current[idx] = el as HTMLImageElement)}/>
                        </div>
                        <div className="cs-title"
                             ref={(el) => (showcaseTitle.current[idx] = el as HTMLDivElement)}>
                            <a href={slide.link}>{slide.name}</a>
                        </div>
                    </div>
                ))}
            </div>

            <div className="cas-titles-wrap">
                <div className="cas-titles" ref={projectTitle}/>
            </div>

            <div className="cas-progress">
                <></>
            </div>

            <div className="showcase-footer">
                <div className="showcase-footer-left">
                    <div className="scroll-notice" data-target="#secondSec">
                        <span className="sn_bef"/>
                        <span>SCROLL</span>
                    </div>
                </div>
                <div className="showcase-footer-right">
                    <div className="a-plus-button"><a href="#">
                        <span><span>ALL PROJECTS</span></span>
                    </a>
                    </div>
                </div>
            </div>
        </div>
    );
}

export default ScrollingCarousel
