import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Table, TableWrapper } from '@/Components/Table';
import Button from '@/Components/Button';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import { useState } from 'react';
import { formatarIbge } from '@/Helpers/IbgeHelper';

export default function Index({ cidades, estados, regionais, filters }) {
    const [estadoFilter, setEstadoFilter] = useState(filters.estado_id || '');
    const [regionalFilter, setRegionalFilter] = useState(filters.regional_id || '');

    const handleFilter = () => {
        router.get(route('cidades.index'), {
            estado_id: estadoFilter,
            regional_id: regionalFilter
        }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setEstadoFilter('');
        setRegionalFilter('');
        router.get(route('cidades.index'), {}, {
            preserveState: true,
            replace: true
        });
    };

    return (
        <AuthenticatedLayout headerTitle="Lista de Cidades">
            <Head title="Cidades" />

            <TableWrapper
                title="Cidades"
                description="Lista de todas as cidades cadastradas"
                actionButton={
                    <Link href={route('cidades.create')}>
                        <Button color="primary">Nova Cidade</Button>
                    </Link>
                }
            >
                <div className="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Filtrar por Estado" />
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
                        <InputLabel value="Filtrar por Regional" />
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

                    <div className="flex items-end space-x-2">
                        <Button onClick={handleFilter} className="h-10">
                            Filtrar
                        </Button>
                        <Button color="secondary" onClick={clearFilters} className="h-10">
                            Limpar
                        </Button>
                    </div>
                </div>

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
                                <div className="flex space-x-2">
                                    <Link
                                        href={route('cidades.edit', cidade.id)}
                                        className="text-indigo-600 hover:text-indigo-900"
                                    >
                                        <i className="fas fa-edit"></i>
                                    </Link>
                                    <Link
                                        href={route('cidades.destroy', cidade.id)}
                                        method="delete"
                                        as="button"
                                        className="text-red-600 hover:text-red-900"
                                        confirm="Tem certeza que deseja excluir esta cidade?"
                                    >
                                        <i className="fas fa-trash"></i>
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
        </AuthenticatedLayout>
    );
}