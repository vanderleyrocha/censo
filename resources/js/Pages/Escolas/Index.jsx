import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Index({ escolas, cidades, filters, dependencias, zonas }) {
    const [searchParams, setSearchParams] = useState({
        cidade_id: filters.cidade_id || '',
        dependencia: filters.dependencia || '',
        zona: filters.zona || ''
    });

    const handleFilterChange = (e) => {
        const { name, value } = e.target;
        setSearchParams(prev => ({
            ...prev,
            [name]: value
        }));
    };

    const applyFilters = () => {
        router.get(route('escolas.index'), searchParams, {
            preserveState: true,
            replace: true
        });
    };

    const resetFilters = () => {
        setSearchParams({
            cidade_id: '',
            dependencia: '',
            zona: ''
        });
        router.get(route('escolas.index'));
    };

    const formatNumber = (value) => {
        if (value == 0 || value == null || value == undefined) return '-';
        return new Intl.NumberFormat('pt-BR').format(value);
    };

    return (
        <AuthenticatedLayout
            headerTitle="Lista de Escolas"
        >
            <Head title="Escolas" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Filtrar Escolas</h3>

                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                                <div>
                                    <label htmlFor="cidade_id" className="block text-sm font-medium text-gray-700 mb-1">
                                        Município
                                    </label>
                                    <select
                                        id="cidade_id"
                                        name="cidade_id"
                                        value={searchParams.cidade_id}
                                        onChange={handleFilterChange}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    >
                                        <option value="">Todos</option>
                                        {cidades.map((cidade) => (
                                            <option key={cidade.id} value={cidade.id}>
                                                {cidade.nome}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label htmlFor="dependencia" className="block text-sm font-medium text-gray-700 mb-1">
                                        Dependência
                                    </label>
                                    <select
                                        id="dependencia"
                                        name="dependencia"
                                        value={searchParams.dependencia}
                                        onChange={handleFilterChange}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    >
                                        <option value="">Todas</option>
                                        {dependencias.map((dependencia, index) => (
                                            <option key={index} value={dependencia}>
                                                {dependencia}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label htmlFor="zona" className="block text-sm font-medium text-gray-700 mb-1">
                                        Zona
                                    </label>
                                    <select
                                        id="zona"
                                        name="zona"
                                        value={searchParams.zona}
                                        onChange={handleFilterChange}
                                        className="w-full rounded-md border-gray-300 shadow-sm focus:border-green-500 focus:ring-green-500"
                                    >
                                        <option value="">Todas</option>
                                        {zonas.map((zona, index) => (
                                            <option key={index} value={zona}>
                                                {zona}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="flex space-x-2">
                                <button
                                    onClick={applyFilters}
                                    className="px-4 py-2 bg-green-600 text-white rounded-md hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-green-500 focus:ring-offset-2"
                                >
                                    Aplicar Filtros
                                </button>
                                <button
                                    onClick={resetFilters}
                                    className="px-4 py-2 bg-gray-200 text-gray-800 rounded-md hover:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-gray-500 focus:ring-offset-2"
                                >
                                    Limpar Filtros
                                </button>
                            </div>
                        </div>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                ID
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nome
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Município
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Dependência
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Zona
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Regional
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Alunos SIMAED
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Alunos Censo 2024
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {escolas.data.map((escola) => (
                                            <tr key={escola.id}>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {escola.id}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                                    {escola.nome}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {escola.cidade?.nome}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {escola.dependencia}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {escola.zona}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                    {escola.cidade?.regional?.nome || 'N/A'}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {formatNumber(escola.alunos_simaed)}
                                                </td>
                                                <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500 text-right">
                                                    {formatNumber(escola.alunos_censo_2024)}
                                                </td>
                                            </tr>
                                        ))}
                                    </tbody>
                                </table>
                            </div>

                            {escolas.data.length === 0 && (
                                <div className="text-center py-4 text-gray-500">
                                    Nenhuma escola encontrada com os filtros aplicados.
                                </div>
                            )}

                            {escolas.data.length > 0 && (
                                <div className="mt-4 flex items-center justify-between">
                                    <div className="text-sm text-gray-700">
                                        Mostrando <span className="font-medium">{escolas.from}</span> a <span className="font-medium">{escolas.to}</span> de <span className="font-medium">{escolas.total}</span> escolas
                                    </div>
                                    <div className="flex space-x-2">
                                        {escolas.prev_page_url && (
                                            <Link
                                                href={escolas.prev_page_url}
                                                preserveState
                                                className="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Anterior
                                            </Link>
                                        )}
                                        {escolas.next_page_url && (
                                            <Link
                                                href={escolas.next_page_url}
                                                preserveState
                                                className="px-3 py-1 border border-gray-300 rounded-md text-sm font-medium text-gray-700 hover:bg-gray-50"
                                            >
                                                Próxima
                                            </Link>
                                        )}
                                    </div>
                                </div>
                            )}
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}