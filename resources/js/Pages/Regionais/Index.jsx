import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { Table, TableWrapper } from '@/Components/Table';
import Button from '@/Components/Button';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import { useState } from 'react';

export default function Index({ regionais, servidores, regioes, filters }) {
    const [servidorFilter, setServidorFilter] = useState(filters.servidor_id || '');
    const [regiaoFilter, setRegiaoFilter] = useState(filters.regiao_id || '');

    const handleFilter = () => {
        router.get(route('regionais.index'), {
            servidor_id: servidorFilter,
            regiao_id: regiaoFilter
        }, {
            preserveState: true,
            replace: true
        });
    };

    const clearFilters = () => {
        setServidorFilter('');
        setRegiaoFilter('');
        router.get(route('regionais.index'), {}, {
            preserveState: true,
            replace: true
        });
    };

    return (
        <AuthenticatedLayout headerTitle="Lista de Regionais">
            <Head title="Regionais" />

            <TableWrapper
                title="Regionais"
                description="Lista de todas as regionais cadastradas"
                actionButton={
                    <Link href={route('regionais.create')}>
                        <Button color="primary">Nova Regional</Button>
                    </Link>
                }
            >
                <div className="mb-6 grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div>
                        <InputLabel value="Filtrar por Servidor" />
                        <SelectInput
                            value={servidorFilter}
                            onChange={(e) => setServidorFilter(e.target.value)}
                            className="w-full"
                        >
                            <option value="">Todos</option>
                            {servidores.map((servidor) => (
                                <option key={servidor.id} value={servidor.id}>
                                    {servidor.nome}
                                </option>
                            ))}
                        </SelectInput>
                    </div>

                    <div>
                        <InputLabel value="Filtrar por Região" />
                        <SelectInput
                            value={regiaoFilter}
                            onChange={(e) => setRegiaoFilter(e.target.value)}
                            className="w-full"
                        >
                            <option value="">Todas</option>
                            {regioes.map((regiao) => (
                                <option key={regiao.id} value={regiao.id}>
                                    {regiao.nome}
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
                    headers={['Nome', 'Sigla', 'Servidor', 'Região', 'Ações']}
                    isEmpty={regionais.data.length === 0}
                    emptyMessage="Nenhuma regional encontrada"
                >
                    {regionais.data.map((regional) => (
                        <tr key={regional.id}>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {regional.nome}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {regional.sigla}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {regional.servidor?.nome || '-'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {regional.regiao?.nome || '-'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div className="flex space-x-2">
                                    <Link
                                        href={route('regionais.edit', regional.id)}
                                        className="text-indigo-600 hover:text-indigo-900"
                                    >
                                        <i className="fas fa-edit"></i>
                                    </Link>
                                    <Link
                                        href={route('regionais.destroy', regional.id)}
                                        method="delete"
                                        as="button"
                                        className="text-red-600 hover:text-red-900"
                                        confirm="Tem certeza que deseja excluir esta regional?"
                                    >
                                        <i className="fas fa-trash"></i>
                                    </Link>
                                </div>
                            </td>
                        </tr>
                    ))}
                </Table>

                {regionais.links.length > 3 && (
                    <div className="mt-4">
                        <nav className="flex justify-center">
                            {regionais.links.map((link, index) => (
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