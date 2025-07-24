import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';

export default function RolesIndex() {
    const { roles, permissions } = usePage().props;

    return (
        <Authenticated>
            <Head title="Gerenciar Roles" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex justify-between items-center mb-6">
                            <h2 className="text-2xl font-bold text-gray-800">Gerenciar Papéis (Roles)</h2>
                            <Link href={route('roles.create')}>
                                <Button>Criar Novo Papel</Button>
                            </Link>
                        </div>

                        <div className="overflow-x-auto">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {roles.map((role) => (
                                        <tr key={role.id}>
                                            <td className="px-6 py-4 whitespace-nowrap font-medium text-gray-900">{role.name}</td>
                                            <td className="px-6 py-4 whitespace-nowrap">
                                                <div className="flex flex-wrap gap-1">
                                                    {role.permissions.map(permission => (
                                                        <span key={permission.id} className="px-2 py-1 bg-blue-100 text-blue-800 text-xs rounded-full">
                                                            {permission.name}
                                                        </span>
                                                    ))}
                                                </div>
                                            </td>
                                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                <Link href={route('roles.edit', role.id)} className="text-indigo-600 hover:text-indigo-900 mr-3">
                                                    Editar
                                                </Link>
                                                <Link
                                                    method="delete"
                                                    href={route('roles.destroy', role.id)}
                                                    as="button"
                                                    className="text-red-600 hover:text-red-900"
                                                    confirm="Tem certeza que deseja excluir este papel?"
                                                >
                                                    Excluir
                                                </Link>
                                            </td>
                                        </tr>
                                    ))}
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}