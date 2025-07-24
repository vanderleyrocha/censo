import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head } from '@inertiajs/react';
import { Bar } from 'react-chartjs-2';
import {
    Chart as ChartJS,
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend,
} from 'chart.js';

// Registrar componentes do Chart.js
ChartJS.register(
    CategoryScale,
    LinearScale,
    BarElement,
    Title,
    Tooltip,
    Legend
);

export default function HomeAutenticada({ escolasEmFuncionamento, distribuicaoPorMunicipio }) {
    // Preparar dados para o gráfico
    const chartData = {
        labels: distribuicaoPorMunicipio.map(item => item.municipio),
        datasets: [
            {
                label: 'Escolas em Funcionamento',
                data: distribuicaoPorMunicipio.map(item => item.total),
                backgroundColor: 'rgba(54, 162, 235, 0.5)',
                borderColor: 'rgba(54, 162, 235, 1)',
                borderWidth: 1,
            },
        ],
    };

    const chartOptions = {
        responsive: true,
        plugins: {
            legend: {
                position: 'top',
            },
            title: {
                display: true,
                text: 'Distribuição de Escolas por Município',
            },
        },
    };

    return (
        <AuthenticatedLayout
            escolasEmFuncionamento={escolasEmFuncionamento}
            headerTitle="Painel de Controle"
        >
            <Head title="Painel de Controle" />

            <div className="py-6">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                        {/* Card Estatísticas */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-green-600">
                            <h3 className="text-lg font-medium text-gray-900">Escolas Cadastradas</h3>
                            <p className="mt-2 text-3xl font-bold text-gray-800">
                                {escolasEmFuncionamento?.toLocaleString('pt-BR') || '0'}
                            </p>
                            <p className="mt-1 text-sm text-gray-500">Total no estado do Acre</p>
                        </div>

                        {/* Card Ações Rápidas */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-blue-600">
                            <h3 className="text-lg font-medium text-gray-900">Ações Rápidas</h3>
                            <div className="mt-4 space-y-2">
                                <a href={route('dashboard')} className="block text-blue-600 hover:text-blue-800">
                                    Escolas por município
                                </a>
                                <a href={route('escolas.tecnico')} className="block text-blue-600 hover:text-blue-800">
                                    Escolas por técnico responsável
                                </a>
                                <a href={route('escolas.atribuir')}
                                    className="block text-blue-600 hover:text-blue-800"
                                    active={route().current('escolas.atribuir')}
                                >
                                    Atribuir Responsável
                                </a>
                            </div>
                        </div>

                        {/* Card Últimas Atualizações */}
                        <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 border-l-4 border-yellow-500">
                            <h3 className="text-lg font-medium text-gray-900">Últimas Atualizações</h3>
                            <div className="mt-4 space-y-3">
                                <div className="text-sm">
                                    <p className="font-medium">Escola X atualizou dados</p>
                                    <p className="text-gray-500">Há 2 horas</p>
                                </div>
                                <div className="text-sm">
                                    <p className="font-medium">Novo município adicionado</p>
                                    <p className="text-gray-500">Ontem</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Gráfico/Seção Principal */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Distribuição de Escolas por Município</h3>
                        <div className="h-96">
                            <Bar data={chartData} options={chartOptions} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}