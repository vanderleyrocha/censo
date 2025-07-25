// Em: /Components/FlashMessage.jsx
import React, { useEffect } from 'react';

export default function FlashMessage({ message, type = 'success', onDismiss }) {
    // Auto-fechamento após 10 segundos
    useEffect(() => {
        const timer = setTimeout(() => {
            if (onDismiss) onDismiss();
        }, 10000);
        return () => clearTimeout(timer);
    }, [onDismiss]);

    if (!message) return null;

    const bgColor = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        warning: 'bg-yellow-500',
        info: 'bg-blue-500',
    }[type];

    return (
        <div className="w-full animate-fade-in mb-4">
            <div className={`${bgColor} text-white px-4 py-2 rounded shadow-lg flex items-center justify-between`}>
                <span>{message}</span>
                <button onClick={onDismiss} className="focus:outline-none">
                    <i className="fas fa-times"></i>
                </button>
            </div>
        </div>
    );
}