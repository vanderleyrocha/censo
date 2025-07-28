export default function SecondaryButton({
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
                `${sizeClasses} border border-gray-300 bg-white text-gray-700 shadow-sm hover:bg-gray-50 focus:ring-indigo-500 ${disabled ? 'opacity-25' : ''
                } ${className}`
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}