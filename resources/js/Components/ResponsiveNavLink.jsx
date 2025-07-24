import { Link } from '@inertiajs/react';

export default function ResponsiveNavLink({
    active = false,
    className = '',
    activeClassName = '', // Agora vamos usar esta prop
    children,
    ...props
}) {
    // Combina as classes: as classes base sempre são aplicadas,
    // e a activeClassName é adicionada apenas se o link estiver ativo.
    const combinedClassName = `${className} ${active ? activeClassName : ''}`;

    return (
        <Link {...props} className={combinedClassName.trim()}>
            {children}
        </Link>
    );
}