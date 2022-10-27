import React from 'react';
import FullscreenHero, { FullscreenHeroProps } from './FullscreenHero';

export default {
    title: "Fullscreenhero",
    component: FullscreenHero
};

export const Default = (props: FullscreenHeroProps) => <FullscreenHero {...props} />;
