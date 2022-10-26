import React from 'react';
import HorizontalCarousel from '@/Components/Carousel/HorizontalCarousel/HorizontalCarousel';
import {carouselShowcase} from '@/Data/carousel';

export default function Carousel() {
    return (
        <HorizontalCarousel slides={carouselShowcase}/>
    );
}
