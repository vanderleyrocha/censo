// resources/js/Components/Table.jsx
import React from 'react';

// Componente TableWrapper
export const TableWrapper = ({ 
    title, 
    description, 
    actionButton, 
    children 
}) => (
    <div className="py-12">
        <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div className="p-6 bg-white border-b border-gray-200">
                    <div className="flex justify-between items-center mb-6">
                        <div>
                            <h2 className="text-2xl font-semibold text-gray-800">{title}</h2>
                            {description && <p className="text-sm text-gray-500 mt-1">{description}</p>}
                        </div>
                        {actionButton}
                    </div>
                    {children}
                </div>
            </div>
        </div>
    </div>
);

// Componente Table
export const Table = ({ 
    headers, 
    children, 
    isEmpty, 
    emptyMessage = 'Nenhum registro encontrado' 
}) => (
    <div className="overflow-x-auto">
        <table className="min-w-full divide-y divide-gray-200">
            <thead className="bg-gray-50">
                <tr>
                    {headers.map((header, index) => (
                        <th 
                            key={index} 
                            className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider"
                        >
                            {header}
                        </th>
                    ))}
                </tr>
            </thead>
            <tbody className="bg-white divide-y divide-gray-200">
                {isEmpty ? (
                    <tr>
                        <td colSpan={headers.length} className="px-6 py-4 text-center text-sm text-gray-500">
                            {emptyMessage}
                        </td>
                    </tr>
                ) : (
                    children
                )}
            </tbody>
        </table>
    </div>
);

// Exportação padrão (opcional)
export default {
    TableWrapper,
    Table
};