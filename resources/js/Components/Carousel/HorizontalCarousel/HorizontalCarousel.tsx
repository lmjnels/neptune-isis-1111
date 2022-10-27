import React, {useLayoutEffect, useRef} from 'react';
import gsap from 'gsap'
import ScrollTrigger from 'gsap/ScrollTrigger'
import SplitText from 'gsap/SplitText'
import './_HorizontalCarousel.scss'
import Section from "../../Section";
import {CarouselSlide} from "../../../data/carousel";
import Plyr from "plyr";

gsap.registerPlugin(SplitText);
gsap.registerPlugin(ScrollTrigger);


export type HorizontalCarouselProps = {
    slides: CarouselSlide[];
}

export default function HorizontalCarousel({slides}: HorizontalCarouselProps) {

    const carouselShowcase = useRef<HTMLDivElement>(null);

    const project = useRef<(HTMLDivElement | any)[]>([]);

    const wrapper = useRef<(HTMLDivElement | any)>(null)

    const projectTitles = useRef(null);

    const headline = useRef<(HTMLDivElement | any)>(null);

    const bgText = useRef(null);

    const wrapFirstTrans = window.outerWidth / 100 * 90;

    const activeProject = null

    const projectTitle = useRef<(HTMLElement | any)[]>([]);

    const projectImage = useRef<(HTMLImageElement | any)[]>([]);

    const progressIndicator = useRef<any>(null);


    const animateCarouselSlides = () => {
        let widthCalc = (wrapper.current.offsetWidth - window.outerWidth + 350)
        // let widthCalc = (wrapper.current.offsetWidth - window.outerWidth + 450)

        console.log('widthCalc', widthCalc)

        const csw = gsap.fromTo(wrapper.current, {
                x: wrapFirstTrans
            }, {x: `-${widthCalc}`}
        );

        new ScrollTrigger({
            trigger: carouselShowcase.current,
            animation: csw,
            pin: true,
            scrub: 1,
            id: 'showcaseScroll',
            start: 'top top',
            end: 'bottom+=3000 top',
            markers: false,
            onUpdate: function (self: ScrollTrigger) {
                const progress = progressIndicator.current;

                gsap.to(progress, {
                    width: self.progress * 100 + '%'
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
    }

    const animateHeadlineText = () => {
        new SplitText(headline.current, {
            type: 'lines',
            linesClass: 'cas-line',
        });

        const queryHeadlineChildren: NodeList = document.querySelectorAll('.cas-line');

        queryHeadlineChildren.forEach((elm: HTMLElement | any) => (
            elm.innerHTML = "<span>" + elm.innerHTML + "</span>"
        ));

        gsap.to('.cas-line span', {
            y: '-100%',
            stagger: 0.01,
            ease: 'none',
            scrollTrigger: {
                trigger: carouselShowcase.current,
                start: 'top top',
                end: '10% top',
                scrub: 1,
                markers: false
            }
        });
    }

    const animateBackgroundText = () => {
        gsap.fromTo(bgText.current, {
            x: '100%'
        }, {
            x: '-30%',
            scrollTrigger: {
                trigger: carouselShowcase.current,
                scrub: 1,
                start: 'top top',
                end: 'bottom+=3000 top',
                markers: false
            }
        });
    }

    const animateCarouselTitles = () => {

        const titleWrapperClassName: string = 'cas-titles-wrap';

        const projectTitleContainer: HTMLElement | any = document.querySelector('.cas-titles');

        const projectTitleWrapper: HTMLElement | any = document.createElement('div');
        projectTitleWrapper.classList.add(titleWrapperClassName);

        project.current.forEach((element, i) => {
            const title = projectTitle;
            // const image = projectImage;

            let idx = i;
            idx++;

            project.current[i].classList.add('cas_project_' + idx);

            title.current[i].classList.add('title_' + idx);


            projectTitleContainer.appendChild(projectTitleWrapper);


            projectTitleWrapper.appendChild(title.current[i]);


        });

        let totProj = project.current.length;
        let transVal = (totProj * 250) - 250

        let mobileQuery = window.matchMedia('(max-width: 450px)');
        let tabletQuery = window.matchMedia('(min-width: 450px) and (max-width: 900px)');

        // Check if the media query is true
        if (mobileQuery.matches)
            transVal = totProj * 80 - 80

        if (tabletQuery.matches)
            transVal = totProj * 200 - 200

        gsap.to(`.${titleWrapperClassName}`, {
            y: `-${transVal}`,
            scrollTrigger: {
                trigger: carouselShowcase.current,
                scrub: 1,
                start: 'top top',
                end: 'bottom+=3000 top',
                markers: false
            }
        });

        /*$('.cs-title').on('mouseenter', function () {
     let $this = $(this);
     $this.addClass('active')
 })*/

        /*$('.cs-title').on('mouseleave', function () {
            let $this = $(this);
            $this.removeClass('active')
        })*/
    }

    const animateCarouselFooter = () => {
        gsap.set('.showcase-footer', {
            position: 'fixed'
        });
    }

    function aliothShowcaseCarousel() {

        animateHeadlineText();

        animateCarouselTitles();

        animateBackgroundText();

        animateCarouselSlides();

        animateCarouselFooter();
    }

    function showcaseOpenings() {
        const sCarouselWelcome = gsap.timeline({
            onStart: function () {
                // disableScroll();
                console.warn('GSAP Timeline started', 'disable scroll')
            },
            onComplete: function () {
                // enableScroll();
                console.warn('GSAP Timeline Completed', 'enable scroll')
            }
        });

        sCarouselWelcome.fromTo('.cas-line', 1,
            {
                y: '100%'
            }, {
                y: '0%',
                stagger: 0.1,
                ease: 'power3.out'
            }, 2
        );

        sCarouselWelcome.fromTo(wrapper.current, 2.5, {
            x: `-${wrapper.current.outerWidth}`,
        }, {
            x: wrapFirstTrans,
            ease: 'circ.inOut',
        }, .2);

        sCarouselWelcome.fromTo('.cas-bg-text', 1.5, {
                x: '-100%'
            }, {
                x: '100%',
                ease: 'power2.out',
            }, .7
        );

        sCarouselWelcome.fromTo(progressIndicator.current, 1.5, {
            width: '0%'
        }, {
            width: '50%',
            ease: 'power2.out',
        }, 2.2);

        sCarouselWelcome.fromTo('.showcase-footer', 1, {
            opacity: 0,
        }, {
            opacity: 1
        }, 2.7);

    }

    useLayoutEffect(() => {

        aliothShowcaseCarousel();

        showcaseOpenings();

        const vid = '.showcase-video';

        const elm = document.querySelector(vid);

        const videoOptions: Plyr.Options = {
            // controls: false,
            autoplay: true,
            clickToPlay: false,
            muted: true,
            autopause: false,
            volume: 0,
            loop: {
                active: true
            },
            vimeo: { transparent: true },
            ratio: '16:9',
            displayDuration: false,
        };

        if(elm){
            new Plyr(vid, videoOptions);
        }


    }, []);

    return (
        <Section>
            <div className="portfolio-showcase carousel-showcase" ref={carouselShowcase}>
                <div className="cas-bg-text" ref={bgText}>Patterned</div>
                <div className="cas-headline" ref={headline}>
                    Hello! We are Patterned Digital<br/>A creative software agency from London.
                    <br/>Proud to deliver bespoke solutions <br/>with a pinch of rock'n roll!
                </div>

                <div className="cas-project-wrapper" ref={wrapper}>

                    {slides.map((slide, idx) => {
                        return (
                            <div className="cas-project"
                                 key={slide.name + '_' + idx}
                                 ref={(el) => (project.current[idx] = el)}>

                                <div className="cs-image">
                                    {slide.media === 'video' && slide.video !== undefined && (
                                        <div className="showcase-video"
                                             data-plyr-provider={slide.provider}
                                             data-plyr-embed-id={slide.video}
                                             ref={(el) => (projectImage.current[idx] = el)}
                                        >
                                        </div>
                                    )}

                                    {slide.media === 'image' && slide.image !== undefined && (
                                        <img
                                            alt={slide.name}
                                            src={slide.image}
                                            ref={(el) => (projectImage.current[idx] = el)}
                                        />
                                    )}
                                </div>

                                <div className="cs-title"
                                     ref={(el) => (projectTitle.current[idx] = el)}
                                >
                                    <a href={slide.link}>{slide.name}</a>
                                </div>

                            </div>
                        )
                    })}


                </div>
                <div className="cas-titles" ref={projectTitles}></div>

                <div className="cas-progress" ref={progressIndicator}>
                    {/*<span>0</span>*/}
                </div>
            </div>
            <div className="showcase-footer">
                <div className="showcase-footer-left">
                    <div className="scroll-notice" data-target="#secondSec">
                        <span className="sn_bef"></span>
                        <span>SCROLL</span>
                    </div>

                </div>
                <div className="showcase-footer-right">
                    <div className="a-plus-button">
                        <a href="#">
                            <span><span>ALL PROJECTS</span></span>
                        </a>
                    </div>
                </div>

            </div>
        </Section>
    );
}
