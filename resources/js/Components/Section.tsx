import React, {ReactNode} from 'react';

export type SectionProps = {
    children: ReactNode|any;
}

export default function Section({ children }: SectionProps) {
    return (
        <div className="section">
            <div className="wrapper no-gap">
                <div className="w-100 no-gap">
                    { children }
                </div>
            </div>
        </div>
    );
}
