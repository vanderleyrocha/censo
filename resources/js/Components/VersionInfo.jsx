import { useEffect, useState } from 'react';

export default function VersionInfo() {
    const [versions, setVersions] = useState(null);
    const [loading, setLoading] = useState(true);

    useEffect(() => {
        fetch('/api/versions')
            .then(response => response.json())
            .then(data => {
                setVersions(data);
                setLoading(false);
            })
            .catch(error => {
                console.error('Erro ao carregar versões:', error);
                setLoading(false);
            });
    }, []);

    if (loading) {
        return (
            <div className="mt-8 p-4 bg-gray-50 rounded-lg border border-gray-200">
                <p className="text-gray-600">Carregando informações de versão...</p>
            </div>
        );
    }

    if (!versions) {
        return null;
    }

    return (
        <div className="mt-8 p-6 bg-gray-50 rounded-lg border border-gray-200">
            <h3 className="text-lg font-semibold text-gray-800 mb-4">Informações da Plataforma</h3>
            
            <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">PHP</h4>
                    <p className="text-sm text-gray-600">{versions.php}</p>
                </div>
                
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">Laravel</h4>
                    <p className="text-sm text-gray-600">{versions.laravel}</p>
                </div>
                
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">React</h4>
                    <p className="text-sm text-gray-600">{versions.react}</p>
                </div>
                
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">PHPSpreadsheet</h4>
                    <p className="text-sm text-gray-600">{versions.phpspreadsheet}</p>
                </div>
                
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">Laravel Permission</h4>
                    <p className="text-sm text-gray-600">{versions.laravel_permission}</p>
                </div>
                
                <div className="bg-white p-3 rounded border">
                    <h4 className="font-medium text-gray-700">Inertia.js</h4>
                    <p className="text-sm text-gray-600">{versions.inertia}</p>
                </div>
            </div>
            
            <div className="mt-4 text-xs text-gray-500">
                <p>Ambiente: {versions.environment} | Última atualização: {versions.timestamp}</p>
            </div>
        </div>
    );
}