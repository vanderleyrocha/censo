import React from 'react';

export default function PermissionCheckbox({ permission, checked, onChange }) {
    return (
        <label className="flex items-center space-x-2 p-2 hover:bg-gray-50 rounded cursor-pointer">
            <input
                type="checkbox"
                checked={checked}
                onChange={onChange}
                className="rounded text-indigo-600 focus:ring-indigo-500"
            />
            <span className="text-sm text-gray-700">{permission}</span>
        </label>
    );
}