import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, usePage } from '@inertiajs/react';
import Button from '@/Components/Button';
import Can from '@/Components/Can';
import { TableWrapper, Table } from '@/Components/Table';

export default function PermissionsIndex() {
    const { permissions } = usePage().props;
    const headers = ['ID', 'Nome', 'Descrição', 'Guard', 'Criado em', 'Ações'];

    return (
        <Authenticated>
            <Head title="Gerenciar Permissões" />

            <TableWrapper
                title="Todas as Permissões"
                description="Gerencie as permissões do sistema"
                actionButton={
                    <Can permission="permissions.create">
                        <Link href={route('permissions.create')}>
                            <Button color="primary" size="sm">
                                <i className="fas fa-plus mr-2"></i> Nova Permissão
                            </Button>
                        </Link>
                    </Can>
                }
            >
                <Table headers={headers} isEmpty={permissions.length === 0}>
                    {permissions.map((permission) => (
                        <tr key={permission.id}>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {permission.id}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {permission.name}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {permission.description || '-'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {permission.guard_name}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {new Date(permission.created_at).toLocaleDateString()}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div className="flex flex-wrap gap-2">
                                    <Can permission="permissions.edit">
                                        <Link href={route('permissions.edit', permission.id)}>
                                            <Button color="edit" size="sm">
                                                <i className="fas fa-edit mr-1"></i> Editar
                                            </Button>
                                        </Link>
                                    </Can>
                                    <Can permission="permissions.delete">
                                        <Link
                                            href={route('permissions.destroy', permission.id)}
                                            method="delete"
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