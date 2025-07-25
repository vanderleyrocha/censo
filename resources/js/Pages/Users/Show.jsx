import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import Button from '@/Components/Button';

export default function UserShow({ user, auth }) {
    return (
        <AuthenticatedLayout
            headerTitle={`Detalhes do Usuário - ${user.name}`}
        >
            <Head title={`Detalhes do Usuário - ${user.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-2xl font-semibold text-gray-800 mb-6">Detalhes do Usuário</h2>

                            <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Informações Básicas</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Nome</label>
                                            <p className="mt-1 text-sm text-gray-900">{user.name}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Email</label>
                                            <p className="mt-1 text-sm text-gray-900">{user.email}</p>
                                        </div>
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Data de Cadastro</label>
                                            <p className="mt-1 text-sm text-gray-900">{new Date(user.created_at).toLocaleDateString()}</p>
                                        </div>
                                    </div>
                                </div>

                                <div>
                                    <h3 className="text-lg font-medium text-gray-900 mb-4">Funções e Permissões</h3>
                                    <div className="space-y-4">
                                        <div>
                                            <label className="block text-sm font-medium text-gray-500">Funções</label>
                                            <div className="mt-1">
                                                {user.roles && user.roles.length > 0 ? (
                                                    <ul className="list-disc list-inside text-sm text-gray-900">
                                                        {user.roles.map(role => (
                                                            <li key={role.id}>{role.name}</li>
                                                        ))}
                                                    </ul>
                                                ) : (
                                                    <p className="text-sm text-gray-500">Nenhuma função atribuída</p>
                                                )}
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <div className="mt-8 flex space-x-4">
                                <Link href={route('users.index')}>
                                    <Button color="secondary">
                                        Voltar
                                    </Button>
                                </Link>
                                <Link href={route('users.editRoles', user.id)}>
                                    <Button>
                                        Editar Funções
                                    </Button>
                                </Link>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}