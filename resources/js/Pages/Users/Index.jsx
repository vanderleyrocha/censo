import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import Button from '@/Components/Button';

export default function UserIndex({ users, auth }) {
    return (
        <AuthenticatedLayout
            headerTitle="Lista de Usuários"
        >
            <Head title="Lista de Usuários" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="flex justify-between items-center mb-6">
                                <h2 className="text-2xl font-semibold text-gray-800">Usuários do Sistema</h2>
                                <Link href={route('users.create')}>
                                    <Button color="primary">
                                        Novo Usuário
                                    </Button>
                                </Link>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nome
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Funções
                                            </th>
                                            <th className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Ações
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {users.map((user) => (
                                            <tr key={user.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                    {user.name}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {user.roles ? user.roles.join(', ') : 'Nenhuma função'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                                    <div className="flex space-x-2">
                                                        {user.servidor_id && (
                                                            <Link href={route('servidores.show', user.servidor_id)}>
                                                                <Button color="secondary" size="sm">
                                                                    Ver Servidor
                                                                </Button>
                                                            </Link>
                                                        )}
                                                        <Link
                                                            href={route('users.editRoles', user.id)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            <Button color="primary" size="sm">
                                                                Editar Funções
                                                            </Button>
                                                        </Link>
                                                        <Link
                                                            href={route('users.edit', user.id)}
                                                            className="text-indigo-600 hover:text-indigo-900"
                                                        >
                                                            <Button color="primary" size="sm">
                                                                Editar
                                                            </Button>
                                                        </Link>
                                                        <Link
                                                            method="delete"
                                                            href={route('users.destroy', user.id)}
                                                            as="button"
                                                            className="text-red-600 hover:text-red-900"
                                                        >
                                                            <Button color="danger" size="sm">
                                                                Excluir
                                                            </Button>
                                                        </Link>
                                                    </div>
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}