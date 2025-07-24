import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, useForm } from '@inertiajs/react';

export default function PermissionsIndex() {
    const { permissions } = usePage().props;

    return (
        <Authenticated>
            <Head title="Gerenciar Permissões" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h2 className="text-2xl font-bold text-gray-800 mb-6">Todas as Permissões</h2>
                        
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            {permissions.map((permission) => (
                                <div key={permission.id} className="bg-gray-50 p-4 rounded-lg">
                                    <h3 className="font-medium text-gray-900">{permission.name}</h3>
                                    <p className="text-sm text-gray-500 mt-1">
                                        Criado em: {new Date(permission.created_at).toLocaleDateString()}
                                    </p>
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}