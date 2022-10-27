import project1 from '@/Images/project-1.jpeg';
import project2 from '@/Images/project-2.jpeg';
import project3 from '@/Images/project-3.jpeg';
import project5 from '@/Images/project-5.jpeg';
import project8 from '@/Images/project-8.jpeg';

export type CarouselSlide = {
    name: string,
    video?: string,
    image?: string,
    link: string,
    category?: string,
    media: string,
    provider?: string,
}

export const carouselShowcase: CarouselSlide[] = [
    {
        name: 'Web Design',
        video: '586727575',
        link: '#',
        category: 'Production',
        media: 'video',
        provider: 'vimeo',
    },
    {
        name: 'Web Design',
        image: project5,
        link: '#',
        category: 'Production',
        media: 'image',
    },
    {
        name: 'App Development',
        image: project8,
        link: '#',
        category: 'Interior',
        media: 'image',
    },
    {
        name: 'Ricardo Almeida',
        image: project1,
        link: '#',
        category: 'Photography',
        media: 'image',
    },
    {
        name: 'The Silk’s Nothing',
        image: project2,
        link: '#',
        category: 'Branding',
        media: 'image',
    },
    {
        name: 'Rainbows',
        image: project3,
        link: '#',
        category: 'Production',
        media: 'image',
    },
]
