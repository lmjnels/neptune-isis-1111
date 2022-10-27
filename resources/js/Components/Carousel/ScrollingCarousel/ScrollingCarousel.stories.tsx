import React from 'react';
import Showcasecarousel, { ShowcasecarouselProps } from './ShowcaseCarousel';

export default {
    title: "Showcasecarousel",
    component: Showcasecarousel
};

export const Default = (props: ShowcasecarouselProps) => <Showcasecarousel {...props} />;
