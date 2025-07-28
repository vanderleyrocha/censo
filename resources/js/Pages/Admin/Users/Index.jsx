import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import Button from '@/Components/Button';
import { TableWrapper, Table } from '@/Components/Table';

export default function UserIndex({ users }) {
    const headers = ['Nome', 'Funções', 'Ações'];

    return (
        <AuthenticatedLayout headerTitle="Lista de Usuários">
            <Head title="Lista de Usuários" />
            
            <TableWrapper
                title="Usuários do Sistema"
                description="Gerencie os usuários com acesso ao sistema"
                actionButton={
                    <Link href={route('users.create')}>
                        <Button color="primary" size="sm">
                            <i className="fas fa-plus mr-2"></i> Novo Usuário
                        </Button>
                    </Link>
                }
            >
                <Table headers={headers} isEmpty={users.length === 0}>
                    {users.map((user) => (
                        <tr key={user.id}>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {user.name}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {user.roles ? user.roles.join(', ') : 'Nenhuma função'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div className="flex flex-wrap gap-2">
                                    {user.servidor_id && (
                                        <Link href={route('servidores.show', user.servidor_id)}>
                                            <Button color="info" size="sm">
                                                <i className="fas fa-eye mr-1"></i> Servidor
                                            </Button>
                                        </Link>
                                    )}
                                    <Link href={route('users.editRoles', user.id)}>
                                        <Button color="edit" size="sm">
                                            <i className="fas fa-user-tag mr-1"></i> Funções
                                        </Button>
                                    </Link>
                                    <Link href={route('users.edit', user.id)}>
                                        <Button color="edit" size="sm">
                                            <i className="fas fa-edit mr-1"></i> Editar
                                        </Button>
                                    </Link>
                                    <Link
                                        method="delete"
                                        href={route('users.destroy', user.id)}
                                        as="button"
                                    >
                                        <Button color="danger" size="sm">
                                            <i className="fas fa-trash mr-1"></i> Excluir
                                        </Button>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>
            </TableWrapper>
        </AuthenticatedLayout>
    );
}