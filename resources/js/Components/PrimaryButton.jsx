export default function PrimaryButton({
    className = '',
    disabled,
    sizeClasses = 'px-4 py-2 text-sm',
    children,
    ...props
}) {
    return (
        <button
            {...props}
            className={
                `${sizeClasses} border border-transparent bg-gray-800 text-white hover:bg-gray-700 focus:bg-gray-700 focus:ring-indigo-500 active:bg-gray-900 ${disabled ? 'opacity-25' : ''
                } ${className}`
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}