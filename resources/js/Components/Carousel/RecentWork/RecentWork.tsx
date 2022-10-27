import React, {useEffect, useRef} from 'react';
import {CarouselSlide} from '@/Data/carousel';
import './_RecentWork.scss'

import gsap from 'gsap';
import {SplitText} from 'gsap/SplitText';
import {ScrollTrigger} from "gsap/ScrollTrigger";


type RecentWorkProps = {
    slides: CarouselSlide[];
}

export default function RecentWork({slides}: RecentWorkProps) {

    const wrapper = useRef(null);
    const wrapperWidth: number = wrapper.current ? wrapper.current.offsetWidth : 0
    // const wrapTransVal = null;
    const wrapTransVal = wrapperWidth - window.outerWidth + window.outerWidth / 2;
    const bgText = useRef(null);
    const parentSec = useRef(null);
    const navigateScroll = useRef(null);


    gsap.registerPlugin(SplitText);
    gsap.registerPlugin(ScrollTrigger);

    useEffect(() => {

        console.log('wrapper', wrapper)
        console.log('wrapperWidth', wrapperWidth)
        console.log('wrapTransVal', wrapTransVal)

        const $this = navigateScroll.current;

        // let $this = $(this),
        //     wrapper = $this.children('.recent-works-wrapper'),
        //     wrapperWidth = wrapper.outerWidth(),
        //     wrapTransVal = wrapperWidth - window.outerWidth + window.outerWidth / 2,
        //     bgText = $this.find('.recent-works-bg-text'),
        //     parentSec = $this.parents('.wrapper'),
        //     navType = $this.data('navigate');
        //
        // $this.addClass('navby-scroll')


        // @todo: causes slides to start in reverse
        const scrollAn = gsap.to(wrapper.current, {
            // x: "-" + wrapTransVal,
            x: "-2658.5"

        });

        /*gsap.to(bgText.current, {
            x: "0%",
            scrollTrigger: {
                trigger: $this,
                start: "top top",
                end: "bottom top",
                scrub: 2,
                pin: true,
                // snap: false,
                pinType: 'fixed',
                pinSpacing: 'margin'
            }
        });*/

        ScrollTrigger.create({
            animation: scrollAn,
            // trigger: $this,
            trigger: '.a-recent-works',
            start: "top top",
            end: "bottom top",
            scrub: 2,
            pin: true,
            // snap: false,
            pinSpacing: 'false',
            // anticipatePin: false,
            pinType: 'fixed'
        });

        // gsap.fromTo($this, {
        //         x: '100%'
        //     },
        //     {
        //         x: '0%',
        //         scrollTrigger: {
        //             trigger: parentSec.current,
        //             pin: false,
        //             start: 'top bottom',
        //             end: 'top top',
        //             scrub: 2
        //         }
        //     })
        //
        // gsap.fromTo($this, {
        //     x: '0%'
        // }, {
        //     x: '-25%',
        //     scrollTrigger: {
        //         trigger: parentSec.current,
        //         pin: false,
        //         scrub: 2,
        //         start: 'bottom bottom',
        //         end: 'bottom top',
        //     }
        // })
    }, [wrapperWidth])

    const Carousel = (slide: CarouselSlide, idx: number) => {
        const {name, image, link, category} = slide;
        idx++;
        const slide_id: string = `recent-work-slide-${idx}`

        return (
            <div className="ar-work" id={slide_id}>
                <a href={link}>
                    <div className="ar-work-image">
                        <img alt={name} src={image}/>
                    </div>
                    <div className="ar-work-title">{name}</div>
                    <div className="ar-work-cat">{category}</div>
                </a>
            </div>
        )
    }

    return (
        <div className="section">
            <div className="wrapper no-gap" ref={parentSec}>
                <div className="w-full gap-0 no-gap">
                    <div className="a-recent-works navby-scroll" ref={navigateScroll}>
                        <div className="recent-works-bg-text" ref={bgText}>
                            Recent Work
                        </div>
                        <div className="a-recent-works-nav">
                            <div className="arw-prev">
                                <i className="icofont-long-arrow-left"/>
                            </div>
                            <div className="arw-next">
                                <i className="icofont-long-arrow-right"/>
                            </div>
                        </div>
                        <div className="recent-works-wrapper" ref={wrapper}>
                            {slides.map((slide: CarouselSlide, idx: number) => {
                                const {name, image, link, category} = slide;
                                idx++;
                                const slide_id: string = `recent-work-slide-${idx}`

                                return (
                                    <div className="ar-work" id={slide_id} key={slide_id}>
                                        <a href={link}>
                                            <div className="ar-work-image">
                                                <img alt={name} src={image}/>
                                            </div>
                                            <div className="ar-work-title">{name}</div>
                                            <div className="ar-work-cat">{category}</div>
                                        </a>
                                    </div>
                                )
                            })}
                        </div>
                    </div>
                </div>
            </div>
        </div>
    );
}
