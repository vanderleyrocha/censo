import React, { useState } from 'react';
import { Head, Link, usePage, useForm } from '@inertiajs/react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import TextInput from '@/Components/TextInput';
import Button from '@/Components/Button';
import ValidationErrors from '@/Components/ValidationErrors';
import PermissionCheckbox from '@/Shared/PermissionCheckbox';

export default function RoleForm({ role = null }) {
    const { permissions } = usePage().props;
    const isEdit = !!role;

    const { data, setData, post, put, processing, errors } = useForm({
        name: role?.name || '',
        permissions: role?.permissions?.map(p => p.name) || []
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        isEdit
            ? put(route('roles.update', role.id))
            : post(route('roles.store'));
    };

    const togglePermission = (permission) => {
        setData('permissions', data.permissions.includes(permission)
            ? data.permissions.filter(p => p !== permission)
            : [...data.permissions, permission]
        );
    };

    return (
        <Authenticated>
            <Head title={isEdit ? "Editar Papel" : "Criar Papel"} />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <ValidationErrors errors={errors} />

                        <form onSubmit={handleSubmit}>
                            <div className="mb-4">
                                <label className="block text-gray-700 text-sm font-bold mb-2" htmlFor="name">
                                    Nome do Papel
                                </label>
                                <TextInput
                                    id="name"
                                    type="text"
                                    value={data.name}
                                    onChange={(e) => setData('name', e.target.value)}
                                    required
                                    className="w-full"
                                />
                            </div>

                            <div className="mb-6">
                                <label className="block text-gray-700 text-sm font-bold mb-2">
                                    Permissões
                                </label>
                                <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-2">
                                    {permissions.map((permission) => (
                                        <PermissionCheckbox
                                            key={permission}
                                            permission={permission}
                                            checked={data.permissions.includes(permission)}
                                            onChange={() => togglePermission(permission)}
                                        />
                                    ))}
                                </div>
                            </div>

                            <div className="flex items-center justify-end">
                                <Link href={route('roles.index')} className="mr-4">
                                    <Button type="button" color="gray">Cancelar</Button>
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {isEdit ? 'Atualizar' : 'Criar'} Papel
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}