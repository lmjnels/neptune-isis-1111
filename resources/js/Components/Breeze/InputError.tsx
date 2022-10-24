
type InputErrorType = {
    message: string;
    className: string;
}
export default function InputError({ message, className = '' }:InputErrorType) {
    return message ? <p className={'text-sm text-red-600 ' + className}>{message}</p> : null;
}
