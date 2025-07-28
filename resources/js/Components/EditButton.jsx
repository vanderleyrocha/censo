export default function EditButton({
    className = '',
    disabled,
    size = 'md',
    children,
    ...props
}) {
    const sizeClasses = {
        sm: 'px-3 py-1 text-xs',
        md: 'px-4 py-2 text-sm',
        lg: 'px-6 py-3 text-base'
    };

    return (
        <button
            {...props}
            className={
                `${sizeClasses[size]} inline-flex items-center rounded-md border border-yellow-300 bg-yellow-50 text-yellow-700 hover:bg-yellow-100 focus:outline-none focus:ring-2 focus:ring-yellow-500 focus:ring-offset-2 font-medium transition-colors ${disabled ? 'opacity-50 cursor-not-allowed' : ''
                } ${className}`
            }
            disabled={disabled}
        >
            {children}
        </button>
    );
}