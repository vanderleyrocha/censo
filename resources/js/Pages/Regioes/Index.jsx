import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import Button from '@/Components/Button';
import { TableWrapper, Table } from '@/Components/Table';
import InputLabel from '@/Components/InputLabel';
import SelectInput from '@/Components/SelectInput';
import { useState, useEffect } from 'react';

export default function Index({ regioes, servidores, filters }) {
    const [servidorFilter, setServidorFilter] = useState(filters.servidor_id || '');

    // Aplica filtros automaticamente quando mudam
    useEffect(() => {
        const timer = setTimeout(() => {
            router.get(route('regioes.index'), {
                servidor_id: servidorFilter
            }, {
                preserveState: true,
                replace: true
            });
        }, 500);

        return () => clearTimeout(timer);
    }, [servidorFilter]);

    const clearFilters = () => {
        setServidorFilter('');
    };

    return (
        <AuthenticatedLayout headerTitle="Lista de Regiões">
            <Head title="Regiões" />
            
            <TableWrapper
                title="Regiões"
                description="Lista de todas as regiões cadastradas"
                actionButton={
                    <Link href={route('regioes.create')}>
                        <Button color="primary" size="sm">
                            <i className="fas fa-plus mr-2"></i> Nova Região
                        </Button>
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

                    <div className="flex items-end">
                        <Button color="secondary" onClick={clearFilters} size="sm" className="h-10">
                            <i className="fas fa-times mr-1"></i> Limpar Filtros
                        </Button>
                    </div>
                </div>

                <Table headers={['Nome', 'Sigla', 'Servidor', 'Ações']} isEmpty={regioes.data.length === 0}>
                    {regioes.data.map((regiao) => (
                        <tr key={regiao.id}>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                {regiao.nome}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {regiao.sigla}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                {regiao.servidor?.nome || '-'}
                            </td>
                            <td className="px-6 py-4 whitespace-nowrap text-sm font-medium">
                                <div className="flex flex-wrap gap-2">
                                    <Link href={route('regioes.edit', regiao.id)}>
                                        <Button color="edit" size="sm">
                                            <i className="fas fa-edit mr-1"></i> Editar
                                        </Button>
                                    </Link>
                                    <Link
                                        method="delete"
                                        href={route('regioes.destroy', regiao.id)}
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

                {regioes.links.length > 3 && (
                    <div className="mt-4">
                        <nav className="flex justify-center">
                            {regioes.links.map((link, index) => (
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