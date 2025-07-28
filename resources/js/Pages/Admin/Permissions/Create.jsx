import React from 'react';
import Authenticated from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import Button from '@/Components/Button';

export default function PermissionCreate() {
    const { data, setData, post, processing, errors } = useForm({
        name: '',
        description: '',
        guard_name: 'web'
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('permissions.store'));
    };

    return (
        <Authenticated>
            <Head title="Criar Nova Permissão" />
            
            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <div className="flex items-center mb-6">
                            <Link 
                                href={route('permissions')} 
                                className="text-gray-500 hover:text-gray-700 mr-4"
                            >
                                <i className="fas fa-arrow-left"></i>
                            </Link>
                            <h2 className="text-2xl font-bold text-gray-800">Criar Nova Permissão</h2>
                        </div>
                        
                        <form onSubmit={submit}>
                            <div className="grid grid-cols-1 gap-6">
                                <div>
                                    <InputLabel htmlFor="name" value="Nome*" />
                                    <TextInput
                                        id="name"
                                        name="name"
                                        value={data.name}
                                        className="mt-1 block w-full"
                                        isFocused={true}
                                        onChange={(e) => setData('name', e.target.value)}
                                        required
                                    />
                                    <InputError message={errors.name} className="mt-2" />
                                </div>
                                
                                <div>
                                    <InputLabel htmlFor="description" value="Descrição" />
                                    <TextInput
                                        id="description"
                                        name="description"
                                        value={data.description}
                                        className="mt-1 block w-full"
                                        onChange={(e) => setData('description', e.target.value)}
                                    />
                                    <InputError message={errors.description} className="mt-2" />
                                </div>
                                
                                <div>
                                    <InputLabel htmlFor="guard_name" value="Guard Name" />
                                    <TextInput
                                        id="guard_name"
                                        name="guard_name"
                                        value={data.guard_name}
                                        className="mt-1 block w-full"
                                        onChange={(e) => setData('guard_name', e.target.value)}
                                    />
                                    <InputError message={errors.guard_name} className="mt-2" />
                                </div>
                            </div>
                            
                            <div className="flex items-center justify-end mt-6">
                                <Link 
                                    href={route('permissions')} 
                                    className="mr-4 text-gray-600 hover:text-gray-800"
                                >
                                    Cancelar
                                </Link>
                                <Button type="submit" disabled={processing}>
                                    {processing ? 'Salvando...' : 'Salvar Permissão'}
                                </Button>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </Authenticated>
    );
}