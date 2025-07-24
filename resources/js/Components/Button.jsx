import PrimaryButton from './PrimaryButton';
import SecondaryButton from './SecondaryButton';
import DangerButton from './DangerButton';

export default function Button({ color = 'primary', ...props }) {
    switch (color) {
        case 'primary':
            return <PrimaryButton {...props} />;
        case 'secondary':
            return <SecondaryButton {...props} />;
        case 'danger':
            return <DangerButton {...props} />;
        case 'gray':
            return <SecondaryButton {...props} className={`bg-gray-100 text-gray-800 hover:bg-gray-200 ${props.className || ''}`} />;
        default:
            return <PrimaryButton {...props} />;
    }
}