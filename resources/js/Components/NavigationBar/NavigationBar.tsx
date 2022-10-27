import React, {createRef, useEffect, useLayoutEffect, useMemo, useRef, useState} from 'react';
import classNames from 'classnames';
import PropTypes from 'prop-types';
import { gsap, Power3 } from 'gsap';
import { CSSRulePlugin } from "gsap/CSSRulePlugin";
import getOuterWindowDimensions from '../../Hooks/useWindowDimensions';
import Logo from '../../Images/site-logo.png';
import './_NavigationBar.scss'
import {navigation} from '../../data/navigation';

import CrossIcon from './CrossIcon';
import HamburgerIcon from './HamburgerIcon';

export type NavigationBarProps = {
    props?: any,
}

function NavigationBar({...props}: NavigationBarProps) {
    gsap.registerPlugin(CSSRulePlugin);

    const [isNavMenuActive, toggleNavMenu] = useState(false);
    const [windowDimensions, outerWindow] = useState(getOuterWindowDimensions());

    const menuWrapper = useRef<HTMLElement>();

    const desktopNavMainMenu = useRef<HTMLElement>();

    const anchorNavLink = useRef([]);

    const navigationRef = useRef<any>(navigation.map(() => createRef()));

    const MainMenuElm = gsap.utils.selector(desktopNavMainMenu);

    const selectorMenuLinks = '.menu-item > a'

    const animation = {
        translateY: 0,
        overwrite: true,
        stagger: .05,
        delay: 2,
        paused: true,
    }

    const mainMenuAnimation = () => {
        gsap.timeline().from('.menu-item', {
            duration: 1.5,
            y: '100%',
            stagger: .2,
            ease: Power3.easeInOut,
            visibility: 'visible',
        }).duration(2)
    }

    const handleNavigationToggle = (event: any): void => {
        event.preventDefault();

        toggleNavMenu(current => !current);
        mainMenuAnimation();
    };

    const NavItems = () => {

        const navSelectors: [] = []

        navigationRef.current.forEach(element => navSelectors.push(element.current));

        console.log('navSelectors', navSelectors);

        // console.log('NavItems', 'navigationRef', navigationRef.current)

        useLayoutEffect(() => {
            // Target the two specific elements we have forwarded refs to
            // gsap.to(navSelectors.current, {
            //     x: 100,
            //     repeat: -1,
            //     repeatDelay: 1,
            //     // rotate: 360,
            //     // y: "500",
            //     ease: "expo",
            //     duration: 1,
            //     yoyo: true
            // });
        })

        return (

            <nav className="menu-wrapper space-y-1" aria-label="Sidebar" ref={(el) => menuWrapper}>
                <ul className="main-menu" ref={(el) => desktopNavMainMenu}>
                    {navigation.map((item, index) => (
                        // <li className="menu-item" key={item.name} ref={anchorNavLink.current[index]}>
                        <li className="menu-item" key={index} ref={navigationRef.current[index]}>
                            <a
                                href={item.href}
                                className={classNames(
                                    item.current ? 'bg-gray-100 text-gray-900' : 'text-gray-600 hover:bg-gray-50 hover:text-gray-900',
                                    'flex items-center px-3 py-2 font-medium rounded-md'
                                )}
                                aria-current={item.current ? 'page' : undefined}
                            >
                                <span className="truncate">{item.name}</span>
                            </a>
                        </li>
                    ))}
                </ul>
            </nav>
        )
    }


    return (
        <div className={ `navbar ${isNavMenuActive && 'navbar-open'}`}>
            <div className="navbar-wrapper flex justify-between block">
                <div className="navbar-logo">
                    <img alt="Site Logo"
                         src={Logo}
                         width="80px"
                    />
                </div>
                <div className="navbar-menu">
                    <button type="button" onClick={e => handleNavigationToggle(e)}>
                        { (!isNavMenuActive) ? <HamburgerIcon/> : <CrossIcon/> }
                    </button>
                </div>
                <div className="site-navigation flex mx-auto">
                    <NavItems/>
                </div>
                <div className="navbar-cta">
                    <a href="#">Start a Project</a>
                </div>
            </div>
        </div>

    );
}

NavigationBar.propTypes = {
    props: PropTypes.any
};

export default NavigationBar;
