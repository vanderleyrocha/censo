import ChartDataLabels from 'chartjs-plugin-datalabels';
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
    Legend,
    ChartDataLabels
);

export default function HomeAutenticada({ escolasEmFuncionamento, distribuicaoPorTecnico }) {
    // Preparar dados para o gráfico
    const chartData = {
        labels: distribuicaoPorTecnico.map(item => item.servidor),
        datasets: [
            {
                label: 'Escolas em Funcionamento',
                data: distribuicaoPorTecnico.map(item => item.total),
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
                text: 'Distribuição de Escolas por técnico responsável',
            },
            datalabels: {
                anchor: 'end',
                align: 'top',
                formatter: (value) => value,
                font: {
                    weight: 'bold'
                }
            }
        },
        scales: {
            y: {
                beginAtZero: true,
                ticks: {
                    precision: 0
                }
            }
        }
    };

    return (
        <AuthenticatedLayout
            header={
                <h2 className="text-xl font-semibold leading-tight text-gray-800">
                    Painel de Controle
                </h2>
            }
            escolasEmFuncionamento={escolasEmFuncionamento}
        >
            <Head title="Painel de Controle" />

            <div className="py-12">
                <div className="max-w-7xl mx-auto sm:px-6 lg:px-8">
                    {/* Gráfico/Seção Principal */}
                    <div className="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 className="text-lg font-medium text-gray-900 mb-4">Distribuição de Escolas por Técnico</h3>
                        <div className="h-96">
                            <Bar data={chartData} options={chartOptions} />
                        </div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}