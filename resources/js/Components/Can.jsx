import { usePage } from '@inertiajs/react';

export default function Can({ permission, children }) {
    const { auth } = usePage().props;
    
    if (auth.user?.permissions.includes(permission) || 
        auth.user?.roles.includes('system-admin')) {
        return children;
    }
    
    return null;
}