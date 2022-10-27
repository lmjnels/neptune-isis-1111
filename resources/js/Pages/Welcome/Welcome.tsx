import React from 'react';
import Carousel from "@/Container/Carousel";
import NavigationBar from "../../Components/NavigationBar/NavigationBar";

export type WelcomeProps = {}

export default function Welcome({}: WelcomeProps) {
    return (
        <>
            <NavigationBar/>
            <Carousel/>
        </>
    );
}
