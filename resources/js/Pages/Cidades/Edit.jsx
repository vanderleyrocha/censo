import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SelectInput from '@/Components/SelectInput';
import InputError from '@/Components/InputError';
import Button from '@/Components/Button';
import { formatarIbge } from '@/Helpers/IbgeHelper';

export default function Edit({ cidade, estados, regionais }) {
    const { data, setData, put, errors, processing } = useForm({
        nome: cidade.nome,
        estado_id: cidade.estado_id,
        regional_id: cidade.regional_id,
        ibge: cidade.ibge ? formatarIbge(String(cidade.ibge).replace(/\D/g, '')) : '',
    });

    const handleSubmit = (e) => {
        e.preventDefault();
        const ibgeUnformatted = data.ibge ? data.ibge.replace(/\D/g, '') : null;

        if (ibgeUnformatted && ibgeUnformatted.length !== 7) {
            alert('O código IBGE deve ter exatamente 7 dígitos');
            return;
        }

        // Obter os parâmetros de filtro da URL atual
        const searchParams = new URLSearchParams(window.location.search);
        const filters = {
            estado_id: searchParams.get('estado_id'),
            regional_id: searchParams.get('regional_id')
        };

        put(route('cidades.update', {
            cidade: cidade.id,
            ...filters // Passa os filtros como parâmetros de query
        }), {
            nome: data.nome,
            estado_id: data.estado_id,
            regional_id: data.regional_id,
            ibge: ibgeUnformatted,
        });
    };

    return (
        <AuthenticatedLayout headerTitle="Editar Cidade">
            <Head title="Editar Cidade" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <form onSubmit={handleSubmit}>
                                <div className="grid grid-cols-1 md:grid-cols-2 gap-6">
                                    <div>
                                        <InputLabel value="Nome" required />
                                        <TextInput
                                            value={data.nome}
                                            onChange={(e) => setData('nome', e.target.value)}
                                            className="w-full mt-1"
                                            required
                                        />
                                        <InputError message={errors.nome} />
                                    </div>

                                    <div>
                                        <InputLabel value="Código IBGE" />
                                        <TextInput
                                            value={data.ibge}
                                            onChange={(e) => {
                                                // Garante que estamos trabalhando com uma string
                                                let value = String(e.target.value).replace(/\D/g, '');

                                                // Restante da lógica de formatação...
                                                if (value.length > 6) {
                                                    value = `${value.substring(0, 2)}.${value.substring(2, 6)}-${value.substring(6, 7)}`;
                                                } else if (value.length > 2) {
                                                    value = `${value.substring(0, 2)}.${value.substring(2)}`;
                                                }

                                                if (value.replace(/\D/g, '').length <= 7) {
                                                    setData('ibge', value);
                                                }
                                            }}
                                            className="w-full mt-1"
                                            placeholder="XX.XXXX-X"
                                        />
                                        <InputError message={errors.ibge} />
                                    </div>

                                    <div>
                                        <InputLabel value="Estado" required />
                                        <SelectInput
                                            value={data.estado_id}
                                            onChange={(e) => setData('estado_id', e.target.value)}
                                            className="w-full mt-1"
                                            required
                                        >
                                            <option value="">Selecione um estado</option>
                                            {estados.map((estado) => (
                                                <option key={estado.id} value={estado.id}>
                                                    {estado.uf} - {estado.nome}
                                                </option>
                                            ))}
                                        </SelectInput>
                                        <InputError message={errors.estado_id} />
                                    </div>

                                    <div>
                                        <InputLabel value="Regional" required />
                                        <SelectInput
                                            value={data.regional_id}
                                            onChange={(e) => setData('regional_id', e.target.value)}
                                            className="w-full mt-1"
                                            required
                                        >
                                            <option value="">Selecione uma regional</option>
                                            {regionais.map((regional) => (
                                                <option key={regional.id} value={regional.id}>
                                                    {regional.nome}
                                                </option>
                                            ))}
                                        </SelectInput>
                                        <InputError message={errors.regional_id} />
                                    </div>
                                </div>

                                <div className="flex justify-end mt-6 space-x-4">
                                    <Link
                                        href={route('cidades.index')}
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