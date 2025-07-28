import PrimaryButton from './PrimaryButton';
import SecondaryButton from './SecondaryButton';
import DangerButton from './DangerButton';
import InfoButton from './InfoButton';
import EditButton from './EditButton';

export default function Button({ color = 'primary', size = 'md', className = '', ...props }) {
    switch (color) {
        case 'primary':
            return <PrimaryButton size={size} className={className} {...props} />;
        case 'secondary':
            return <SecondaryButton size={size} className={className} {...props} />;
        case 'danger':
            return <DangerButton size={size} className={className} {...props} />;
        case 'info':
            return <InfoButton size={size} className={className} {...props} />;
        case 'edit':
            return <EditButton size={size} className={className} {...props} />;
        case 'gray':
            return <SecondaryButton size={size} className={`bg-gray-100 text-gray-800 hover:bg-gray-200 ${className}`} {...props} />;
        default:
            return <PrimaryButton size={size} className={className} {...props} />;
    }
}