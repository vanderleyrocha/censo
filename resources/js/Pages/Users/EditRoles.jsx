import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import Checkbox from '@/Components/Checkbox';

export default function EditRoles({ user, roles, auth }) {
    const { data, setData, put, processing, errors } = useForm({
        roles: user.roles ? user.roles.map(role => role.id) : []
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('users.updateRoles', user.id));
    };

    return (
        <AuthenticatedLayout
            headerTitle={`Editar Funções - ${user.name}`}
        >
            <Head title={`Editar Funções - ${user.name}`} />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h2 className="text-lg font-medium text-gray-900 mb-4">Editar Funções do Usuário: {user.name}</h2>

                            <form onSubmit={handleSubmit}>
                                <div className="space-y-4">
                                    {roles.map((role) => (
                                        <div key={role.id} className="flex items-center">
                                            <Checkbox
                                                id={`role-${role.id}`}
                                                name="roles[]"
                                                value={role.id}
                                                checked={data.roles.includes(role.id)}
                                                onChange={(e) => {
                                                    if (e.target.checked) {
                                                        setData('roles', [...data.roles, role.id]);
                                                    } else {
                                                        setData('roles', data.roles.filter(id => id !== role.id));
                                                    }
                                                }}
                                            />
                                            <label htmlFor={`role-${role.id}`} className="ml-2 text-sm text-gray-700">
                                                {role.name}
                                            </label>
                                        </div>
                                    ))}
                                </div>

                                <div className="flex items-center justify-end mt-6">
                                    <Link
                                        href={route('users.index')}
                                        className="text-gray-600 hover:text-gray-900 mr-4"
                                    >
                                        <Button color="secondary">
                                            Cancelar
                                        </Button>
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        {processing ? 'Salvando...' : 'Salvar Alterações'}
                                    </Button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}