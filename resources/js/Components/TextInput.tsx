import React, {ChangeEvent, SyntheticEvent, useEffect, useRef} from 'react';

type TextInputType = {
    type: string;
    name: string;
    value: string;
    className: string;
    autoComplete?: any;
    required?: boolean,
    isFocused?: boolean,
    handleChange: any;
}

export default function TextInput({
    type = 'text',
    name,
    value,
    className,
    autoComplete,
    required = true,
    isFocused = false,
    handleChange,
}:TextInputType) {
    const input = useRef();

    useEffect(() => {
        if (isFocused) {
            input.current.focus();
        }
    }, []);

    return (
        <div className="flex flex-col items-start">
            <input
                type={type}
                name={name}
                value={value}
                className={
                    `border-gray-300 focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50 rounded-md shadow-sm ` +
                    className
                }
                ref={input}
                autoComplete={autoComplete}
                required={required}
                onChange={(e:ChangeEvent) => handleChange(e)}
            />
        </div>
    );
}
