import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SelectInput from '@/Components/SelectInput';
import InputError from '@/Components/InputError';
import Button from '@/Components/Button';

export default function Edit({ regional, servidores, regioes }) {
    const { data, setData, put, errors, processing } = useForm({
        nome: regional.nome,
        sigla: regional.sigla,
        servidor_id: regional.servidor_id,
        regiao_id: regional.regiao_id
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        put(route('regionais.update', regional.id));
    };

    return (
        <AuthenticatedLayout headerTitle="Editar Regional">
            <Head title="Editar Regional" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel value="Nome" />
                                        <TextInput
                                            value={data.nome}
                                            onChange={(e) => setData('nome', e.target.value)}
                                            className="w-full mt-1"
                                            required
                                        />
                                        <InputError message={errors.nome} />
                                    </div>

                                    <div>
                                        <InputLabel value="Sigla" />
                                        <TextInput
                                            value={data.sigla}
                                            onChange={(e) => setData('sigla', e.target.value)}
                                            className="w-full mt-1"
                                            required
                                        />
                                        <InputError message={errors.sigla} />
                                    </div>

                                    <div>
                                        <InputLabel value="Servidor Responsável" />
                                        <SelectInput
                                            value={data.servidor_id}
                                            onChange={(e) => setData('servidor_id', e.target.value)}
                                            className="w-full mt-1"
                                        >
                                            <option value="">Selecione um servidor</option>
                                            {servidores.map((servidor) => (
                                                <option key={servidor.id} value={servidor.id}>
                                                    {servidor.nome}
                                                </option>
                                            ))}
                                        </SelectInput>
                                        <InputError message={errors.servidor_id} />
                                    </div>

                                    <div>
                                        <InputLabel value="Região" />
                                        <SelectInput
                                            value={data.regiao_id}
                                            onChange={(e) => setData('regiao_id', e.target.value)}
                                            className="w-full mt-1"
                                        >
                                            <option value="">Selecione uma região</option>
                                            {regioes.map((regiao) => (
                                                <option key={regiao.id} value={regiao.id}>
                                                    {regiao.nome}
                                                </option>
                                            ))}
                                        </SelectInput>
                                        <InputError message={errors.regiao_id} />
                                    </div>
                                </div>

                                <div className="flex justify-end mt-6 space-x-4">
                                    <Link
                                        href={route('regionais.index')}
                                        className="inline-flex items-center px-4 py-2 bg-gray-200 border border-transparent rounded-md font-semibold text-gray-700 hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 transition ease-in-out duration-150"
                                    >
                                        Cancelar
                                    </Link>
                                    <Button type="submit" disabled={processing}>
                                        Atualizar
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