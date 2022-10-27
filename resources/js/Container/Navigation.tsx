import React from 'react';
import RecentWork from '@/Components/Carousel/RecentWork/RecentWork';
import FullscreenHero from '@/Components/Carousel/FullscreenHero/FullscreenHero';
import HorizontalCarousel from '@/Components/Carousel/HorizontalCarousel/HorizontalCarousel';
import {carouselShowcase} from '@/Data/carousel';

export default function Navigation() {
    return (
        <HorizontalCarousel slides={carouselShowcase}/>
    );
}
