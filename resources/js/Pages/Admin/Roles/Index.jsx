import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import Button from '@/Components/Button';
import Can from '@/Components/Can';
import { TableWrapper, Table } from '@/Components/Table';

export default function RolesIndex() {
    const { roles, permissions } = usePage().props;
    const headers = ['Nome', 'Permissões', 'Ações'];

    return (
        <Authenticated>
            <Head title="Gerenciar Funções" />

            <TableWrapper
                title="Gerenciar Funções"
                description="Gerencie as funções e suas permissões no sistema"
                actionButton={
                    <Can permission="create-roles">
                        <Link href={route('roles.create')}>
                            <Button color="primary" size="sm">
                                <i className="fas fa-plus mr-2"></i> Criar Nova Função
                            </Button>
                        </Link>
                    </Can>
                }
            >
                <Table headers={headers} isEmpty={roles.length === 0} emptyMessage="Nenhum papel cadastrado no sistema">
                    {roles.map((role) => (
                        <tr key={role.id} className="hover:bg-gray-50 transition-colors">
                            <td className="px-6 py-4 whitespace-nowrap">
                                <div className="text-sm text-gray-900">{role.name}</div>
                                <div className="text-sm text-gray-500">{role.description || 'Sem descrição'}</div>
                            </td>
                            <td className="px-6 py-4">
                                <div className="flex flex-wrap gap-2">
                                    {role.permissions.length > 0 ? (
                                        role.permissions.map(permission => (
                                            <span
                                                key={permission.id}
                                                className="px-3 py-1 bg-blue-50 text-blue-700 text-xs font-medium rounded-full"
                                            >
                                                {permission.name}
                                            </span>
                                        ))
                                    ) : (
                                        <span className="text-sm text-gray-500">Nenhuma permissão associada</span>
                                    )}
                                </div>
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div className="flex flex-wrap gap-2">
                                    <Can permission="edit-roles">
                                        <Link href={route('roles.edit', role.id)}>
                                            <Button color="edit" size="sm">
                                                <i className="fas fa-edit mr-1"></i> Editar
                                            </Button>
                                        </Link>
                                    </Can>
                                    <Can permission="delete-roles">
                                        <Link
                                            method="delete"
                                            href={route('roles.destroy', role.id)}
                                            as="button"
                                        >
                                            <Button color="danger" size="sm">
                                                <i className="fas fa-trash mr-1"></i> Excluir
                                            </Button>
                                        </Link>
                                    </Can>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>
            </TableWrapper>
        </Authenticated>
    );
}