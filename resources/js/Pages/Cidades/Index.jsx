import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Table, TableWrapper } from '@/Components/Table';
import Button from '@/Components/Button';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import { useEffect, useState } from 'react';
import { formatarIbge } from '@/Helpers/IbgeHelper';
import debounce from 'lodash/debounce';

export default function Index({ cidades, estados, regionais, filters }) {
    const [estadoFilter, setEstadoFilter] = useState(filters.estado_id || '');
    const [regionalFilter, setRegionalFilter] = useState(filters.regional_id || '');

    // Aplicar filtros automaticamente com debounce
    const applyFilters = debounce(() => {
        router.get(route('cidades.index'), {
            estado_id: estadoFilter,
            regional_id: regionalFilter
        }, {
            preserveState: true,
            replace: true,
            preserveScroll: true
        });
    }, 500);

    // Limpar filtros
    const clearFilters = () => {
        setEstadoFilter('');
        setRegionalFilter('');
        router.get(route('cidades.index'), {}, {
            preserveState: true,
            replace: true
        });
    };

    // Aplicar filtros automaticamente quando os valores mudam
    useEffect(() => {
        applyFilters();
        return () => applyFilters.cancel();
    }, [estadoFilter, regionalFilter]);

    return (
        <AuthenticatedLayout headerTitle="Lista de Cidades">
            <Head title="Cidades" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-4">
                        <div className="p-6 bg-white border-b border-gray-200">
                            <h3 className="text-lg font-medium text-gray-900 mb-4">Filtros</h3>
                            <div className="grid grid-cols-1 md:grid-cols-3 gap-4">
                                <div>
                                    <InputLabel value="Estado" />
                                    <SelectInput
                                        value={estadoFilter}
                                        onChange={(e) => setEstadoFilter(e.target.value)}
                                        className="w-full"
                                    >
                                        <option value="">Todos</option>
                                        {estados.map((estado) => (
                                            <option key={estado.id} value={estado.id}>
                                                {estado.nome} ({estado.uf})
                                            </option>
                                        ))}
                                    </SelectInput>
                                </div>

                                <div>
                                    <InputLabel value="Regional" />
                                    <SelectInput
                                        value={regionalFilter}
                                        onChange={(e) => setRegionalFilter(e.target.value)}
                                        className="w-full"
                                    >
                                        <option value="">Todas</option>
                                        {regionais.map((regional) => (
                                            <option key={regional.id} value={regional.id}>
                                                {regional.nome}
                                            </option>
                                        ))}
                                    </SelectInput>
                                </div>

                                <div className="flex items-end">
                                    <Button color="secondary" onClick={clearFilters} size="sm">
                                        <i className="fas fa-times mr-1"></i> Limpar Filtros
                                    </Button>
                                </div>
                            </div>
                        </div>
                    </div>

                    <TableWrapper
                        title="Cidades"
                        description="Lista de todas as cidades cadastradas"
                        actionButton={
                            <Link href={route('cidades.create')}>
                                <Button color="primary" size="sm">
                                    <i className="fas fa-plus mr-2"></i> Nova Cidade
                                </Button>
                            </Link>
                        }
                    >
                        <Table
                            headers={['Nome', 'Estado', 'Regional', 'Código IBGE', 'Ações']}
                            isEmpty={cidades.data.length === 0}
                            emptyMessage="Nenhuma cidade encontrada"
                        >
                            {cidades.data.map((cidade) => (
                                <tr key={cidade.id}>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                        {cidade.nome}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {cidade.estado?.uf} - {cidade.estado?.nome}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {cidade.regional?.nome || '-'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {cidade.ibge ? formatarIbge(cidade.ibge) : '-'}
                                    </td>
                                    <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                        <div className="flex flex-wrap gap-2">
                                            <Link href={route('cidades.edit', cidade.id)}>
                                                <Button color="edit" size="sm">
                                                    <i className="fas fa-edit mr-1"></i> Editar
                                                </Button>
                                            </Link>
                                            <Link
                                                href={route('cidades.destroy', cidade.id)}
                                                method="delete"
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

                        {cidades.links.length > 3 && (
                            <div className="mt-4">
                                <nav className="flex justify-center">
                                    {cidades.links.map((link, index) => (
                                        <Link
                                            key={index}
                                            href={link.url || '#'}
                                            className={`px-4 py-2 mx-1 rounded-md ${link.active ? 'bg-green-800 text-white' : 'bg-white text-gray-700'} ${!link.url ? 'opacity-50 cursor-not-allowed' : 'hover:bg-gray-100'}`}
                                            dangerouslySetInnerHTML={{ __html: link.label }}
                                        />
                                    ))}
                                </nav>
                            </div>
                        )}
                    </TableWrapper>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}