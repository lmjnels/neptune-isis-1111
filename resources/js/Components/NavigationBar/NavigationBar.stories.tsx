import React from 'react';
import Navigationbar, { NavigationbarProps } from './NavigationBar';

export default {
    title: "Navigationbar",
    component: Navigationbar
};

export const Default = (props: NavigationbarProps) => <Navigationbar {...props} />;
