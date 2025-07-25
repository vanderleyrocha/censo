import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Can from '@/Components/Can';

export default function RolesIndex() {
    const { roles, permissions } = usePage().props;

    return (
        <Authenticated>
            <Head title="Gerenciar Funções" />

            <div className="py-8">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-xl sm:rounded-lg p-6">
                        <div className="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4 mb-8">
                            <div>
                                <h2 className="text-2xl font-bold text-gray-900">Gerenciar Funções</h2>
                                <p className="text-sm text-gray-500 mt-1">
                                    Gerencie as funções e suas permissões no sistema
                                </p>
                            </div>
                            <Can permission="create-roles">
                                <Link href={route('roles.create')}>
                                    <Button className="w-full sm:w-auto flex items-center gap-2">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                                        </svg>
                                        Criar Nova Função
                                    </Button>
                                </Link>
                            </Can>
                        </div>

                        <div className="overflow-x-auto rounded-lg border border-gray-200">
                            <table className="min-w-full divide-y divide-gray-200">
                                <thead className="bg-gray-50">
                                    <tr>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nome</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Permissões</th>
                                        <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Ações</th>
                                    </tr>
                                </thead>
                                <tbody className="bg-white divide-y divide-gray-200">
                                    {roles.length > 0 ? (
                                        roles.map((role) => (
                                            <tr key={role.id} className="hover:bg-gray-50 transition-colors">
                                                <td className="px-6 py-4 whitespace-nowrap">
                                                    <div className="font-medium text-gray-900">{role.name}</div>
                                                    <div className="text-sm text-gray-500">{role.description || 'Sem descrição'}</div>
                                                </td>
                                                <td className="px-6 py-4">
                                                    <div className="flex flex-wrap gap-2">
                                                        {role.permissions.length > 0 ? (
                                                            role.permissions.map(permission => (
                                                                <span
                                                                    key={permission.id}
                                                                    className="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full flex items-center gap-1"
                                                                >
                                                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-3 w-3" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z" />
                                                                    </svg>
                                                                    {permission.name}
                                                                </span>
                                                            ))
                                                        ) : (
                                                            <span className="text-sm text-gray-500">Nenhuma permissão associada</span>
                                                        )}
                                                    </div>
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium space-x-2">
                                                    <Can permission="edit-roles">
                                                        <Link
                                                            href={route('roles.edit', role.id)}
                                                            className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-indigo-700 bg-indigo-100 hover:bg-indigo-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500"
                                                        >
                                                            Editar
                                                        </Link>
                                                    </Can>
                                                    <Can permission="delete-roles">
                                                        <Link
                                                            method="delete"
                                                            href={route('roles.destroy', role.id)}
                                                            as="button"
                                                            className="inline-flex items-center px-3 py-1.5 border border-transparent text-xs font-medium rounded text-red-700 bg-red-100 hover:bg-red-200 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-red-500"
                                                            confirm="Tem certeza que deseja excluir este papel?"
                                                        >
                                                            Excluir
                                                        </Link>
                                                    </Can>
                                                </td>
                                            </tr>
                                        ))
                                    ) : (
                                        <tr>
                                            <td colSpan="3" className="px-6 py-4 text-center text-sm text-gray-500">
                                                Nenhum papel cadastrado no sistema
                                            </td>
                                        </tr>
                                    )}
                                </tbody>
                            </table>
                        </div>

                        {roles.length > 0 && (
                            <div className="mt-4 text-sm text-gray-500">
                                Mostrando {roles.length} {roles.length === 1 ? 'papel' : 'funções'}
                            </div>
                        )}
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}