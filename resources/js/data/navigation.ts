// @ts-ignore

interface NavigationAnchor {
    name: string,
    href: string,
    current: boolean,
    target?: string,
    rel?: string,
    // [key: string]: any; // 👈️ index signature - extendable type
}

export const navigation: NavigationAnchor[] = [
    { name: 'Home', href: '#', current: true },
    { name: 'About Us', href: '#', current: false },
    { name: 'What We Do', href: '#', current: false },
    { name: 'Case Studies', href: '#', current: false },
    { name: 'Estimate Project', href: '#', current: false },
    { name: 'Contact Us', href: '#', current: false },
]
