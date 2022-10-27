import React from 'react';
import Recentwork, { RecentworkProps } from './RecentWork';

export default {
    title: "Recentwork",
    component: Recentwork
};

export const Default = (props: RecentworkProps) => <Recentwork {...props} />;
