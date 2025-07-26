import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useState } from 'react';

export default function Atribuir({ escolas, filters, cidades, dependencias, zonas, tecnicos, auth, headerTitle, canEdit, userRegionalIds }) {
    const [selectedSchools, setSelectedSchools] = useState([]);
    const [servidorId, setServidorId] = useState('');

    const handleFilter = (e) => {
        e.preventDefault();
        router.get(route('escolas.atribuir'), filters, {
            preserveState: true,
            replace: true,
        });
    };

    const handleResetFilters = () => {
        router.get(route('escolas.atribuir'), {}, {
            preserveState: true,
            replace: true,
        });
    };

    const toggleSchoolSelection = (id) => {
        if (!canEdit) return;
        
        setSelectedSchools(prev => 
            prev.includes(id) 
                ? prev.filter(schoolId => schoolId !== id) 
                : [...prev, id]
        );
    };

    const selectAllSchools = () => {
        if (!canEdit) return;
        
        // Get all schools that belong to any of the user's regionals
        const escolasDasRegionais = escolas.filter(escola => 
            userRegionalIds.includes(escola.cidade?.regional_id)
        );
        
        if (selectedSchools.length === escolasDasRegionais.length) {
            setSelectedSchools([]);
        } else {
            setSelectedSchools(escolasDasRegionais.map(escola => escola.id));
        }
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        router.post(route('escolas.atualizar-responsavel'), {
            escolas_ids: selectedSchools,
            servidor_id: servidorId,
        }, {
            preserveScroll: true,
        });
    };

    const showCidadeColumn = !filters.cidade_id;
    const showDependenciaColumn = !filters.dependencia;
    const showZonaColumn = !filters.zona;

    const canEditSchool = (escola) => {
        const adminRoles = ['system-admin', 'state-admin', 'regional-admin'];
        const hasAdminRole = auth.user.roles?.some(role => adminRoles.includes(role));
        return (canEdit && userRegionalIds.includes(escola.cidade?.regional_id)) || hasAdminRole;
    };

    return (
        <AuthenticatedLayout headerTitle={headerTitle}>
            <Head title="Atribuir Responsável" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                        <form onSubmit={handleFilter} className="space-y-4">
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <label htmlFor="cidade_id" className="block text-sm font-medium text-gray-700">
                                        Cidade
                                    </label>
                                    <select
                                        id="cidade_id"
                                        name="cidade_id"
                                        value={filters.cidade_id || ''}
                                        onChange={(e) => router.get(route('escolas.atribuir'), { ...filters, cidade_id: e.target.value }, { preserveState: true })}
                                        className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Todas as cidades</option>
                                        {cidades.map((cidade) => (
                                            <option key={cidade.id} value={cidade.id}>
                                                {cidade.nome}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label htmlFor="dependencia" className="block text-sm font-medium text-gray-700">
                                        Dependência
                                    </label>
                                    <select
                                        id="dependencia"
                                        name="dependencia"
                                        value={filters.dependencia || ''}
                                        onChange={(e) => router.get(route('escolas.atribuir'), { ...filters, dependencia: e.target.value }, { preserveState: true })}
                                        className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Todas as dependências</option>
                                        {dependencias.map((dependencia) => (
                                            <option key={dependencia} value={dependencia}>
                                                {dependencia}
                                            </option>
                                        ))}
                                    </select>
                                </div>

                                <div>
                                    <label htmlFor="zona" className="block text-sm font-medium text-gray-700">
                                        Zona
                                    </label>
                                    <select
                                        id="zona"
                                        name="zona"
                                        value={filters.zona || ''}
                                        onChange={(e) => router.get(route('escolas.atribuir'), { ...filters, zona: e.target.value }, { preserveState: true })}
                                        className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md"
                                    >
                                        <option value="">Todas as zonas</option>
                                        {zonas.map((zona) => (
                                            <option key={zona} value={zona}>
                                                {zona}
                                            </option>
                                        ))}
                                    </select>
                                </div>
                            </div>

                            <div className="flex justify-end space-x-3">
                                <button
                                    type="button"
                                    onClick={handleResetFilters}
                                    className="inline-flex items-center px-4 py-2 border border-gray-300 shadow-sm text-sm font-medium rounded-md text-gray-700 bg-white hover:bg-gray-50 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Limpar Filtros
                                </button>
                                <button
                                    type="submit"
                                    className="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500"
                                >
                                    Filtrar
                                </button>
                            </div>
                        </form>
                    </div>

                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <form onSubmit={handleSubmit}>
                            <div className="mb-4">
                                <label htmlFor="servidor_id" className="block text-sm font-medium text-gray-700">
                                    Selecionar Técnico Responsável
                                </label>
                                <select
                                    id="servidor_id"
                                    name="servidor_id"
                                    value={servidorId}
                                    onChange={(e) => setServidorId(e.target.value)}
                                    className="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 focus:outline-none focus:ring-green-500 focus:border-green-500 sm:text-sm rounded-md"
                                    required
                                    disabled={!canEdit}
                                >
                                    <option value="">Selecione um técnico</option>
                                    {tecnicos && tecnicos.map(tecnico => (
                                        <option key={tecnico.id} value={tecnico.id}>
                                            {tecnico.nome}
                                        </option>
                                    ))}
                                    <option value={auth.user.servidor_id}>{auth.user.name} (Você)</option>
                                </select>
                            </div>

                            <div className="overflow-x-auto">
                                <table className="min-w-full divide-y divide-gray-200">
                                    <thead className="bg-gray-50">
                                        <tr>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                {canEdit && (
                                                    <input
                                                        type="checkbox"
                                                        checked={selectedSchools.length === escolas.filter(e => userRegionalIds.includes(e.cidade?.regional_id)).length && escolas.length > 0}
                                                        onChange={selectAllSchools}
                                                        className="h-4 w-4 text-green-600 focus:ring-green-500 border-gray-300 rounded"
                                                    />
                                                )}
                                            </th>
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Nome da Escola
                                            </th>
                                            {showCidadeColumn && (
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Cidade
                                                </th>
                                            )}
                                            {showDependenciaColumn && (
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Dependência
                                                </th>
                                            )}
                                            {showZonaColumn && (
                                                <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                    Zona
                                                </th>
                                            )}
                                            <th scope="col" className="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">
                                                Responsável Atual
                                            </th>
                                        </tr>
                                    </thead>
                                    <tbody className="bg-white divide-y divide-gray-200">
                                        {escolas.length > 0 ? (
                                            escolas.map((escola) => {
                                                const isFromUserRegional = userRegionalIds.includes(escola.cidade?.regional_id);
                                                const canEditThisSchool = canEditSchool(escola);
                                                const rowClass = selectedSchools.includes(escola.id) 
                                                    ? 'bg-green-50' 
                                                    : !isFromUserRegional 
                                                        ? 'bg-gray-50' 
                                                        : '';

                                                return (
                                                    <tr key={escola.id} className={rowClass}>
                                                        <td className="px-6 py-4 whitespace-nowrap">
                                                            {canEdit && (
                                                                <input
                                                                    type="checkbox"
                                                                    checked={selectedSchools.includes(escola.id)}
                                                                    onChange={() => toggleSchoolSelection(escola.id)}
                                                                    disabled={!canEditThisSchool}
                                                                    className={`h-4 w-4 ${canEditThisSchool ? 'text-green-600 focus:ring-green-500' : 'text-gray-400'} border-gray-300 rounded`}
                                                                />
                                                            )}
                                                        </td>
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                                            {escola.nome}
                                                        </td>
                                                        {showCidadeColumn && (
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {escola.cidade?.nome}
                                                                {!isFromUserRegional && (
                                                                    <span className="ml-2 text-xs text-gray-400">(outra regional)</span>
                                                                )}
                                                            </td>
                                                        )}
                                                        {showDependenciaColumn && (
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {escola.dependencia}
                                                            </td>
                                                        )}
                                                        {showZonaColumn && (
                                                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                                {escola.zona}
                                                            </td>
                                                        )}
                                                        <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                                            {escola.responsavel?.nome || 'Não definido'}
                                                        </td>
                                                    </tr>
                                                );
                                            })
                                        ) : (
                                            <tr>
                                                <td colSpan={5 + (showCidadeColumn ? 1 : 0) + (showDependenciaColumn ? 1 : 0) + (showZonaColumn ? 1 : 0)} className="px-6 py-4 text-center text-sm text-gray-500">
                                                    Nenhuma escola encontrada com os filtros selecionados.
                                                </td>
                                            </tr>
                                        )}
                                    </tbody>
                                </table>
                            </div>

                            {escolas.length > 0 && canEdit && (
                                <div className="mt-4 flex justify-end">
                                    <button
                                        type="submit"
                                        disabled={selectedSchools.length === 0 || !servidorId}
                                        className={`inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md shadow-sm text-white ${selectedSchools.length === 0 || !servidorId ? 'bg-gray-400 cursor-not-allowed' : 'bg-green-600 hover:bg-green-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-green-500'}`}
                                    >
                                        Atribuir Técnico Selecionado
                                    </button>
                                </div>
                            )}
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}