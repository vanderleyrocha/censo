import { Link } from '@inertiajs/react';
import { useState } from 'react';

export default function GuestLayout({ children }) {
    const [sidebarOpen, setSidebarOpen] = useState(false);

    return (
        <div className="min-h-screen flex flex-col bg-gray-50">
            {/* Sidebar e Conteúdo Principal */}
            <div className="flex flex-1">
                {/* Sidebar */}
                <div className={`${sidebarOpen ? 'w-64' : 'w-20'} bg-green-800 text-white transition-all duration-300 fixed h-full z-10`}>
                    <div className="p-4 flex items-center justify-between border-b border-green-700">
                        {sidebarOpen ? (
                            <Link href="/" className="flex items-center">
                                <img className="h-10 w-auto" src="/images/logo.png" alt="Logo Governo do Acre" />
                                <span className="ml-3 text-white font-semibold text-sm">Censo Escolar</span>
                            </Link>
                        ) : (
                            <Link href="/">
                                <img className="h-10 w-auto" src="/images/logo.png" alt="Logo Governo do Acre" />
                            </Link>
                        )}
                        <button 
                            onClick={() => setSidebarOpen(!sidebarOpen)}
                            className="text-green-200 hover:text-white"
                        >
                            {sidebarOpen ? (
                                <i className="fas fa-chevron-left"></i>
                            ) : (
                                <i className="fas fa-chevron-right"></i>
                            )}
                        </button>
                    </div>

                    <nav className="mt-4">
                        <div className="space-y-1 px-2">
                            <Link 
                                href={route('login')} 
                                className={`flex items-center p-2 rounded hover:bg-green-700 ${route().current('login') ? 'bg-green-900' : ''}`}
                            >
                                <i className="fas fa-sign-in-alt mr-3"></i>
                                {sidebarOpen && <span>Login</span>}
                            </Link>
                            <Link 
                                href={route('register')} 
                                className={`flex items-center p-2 rounded hover:bg-green-700 ${route().current('register') ? 'bg-green-900' : ''}`}
                            >
                                <i className="fas fa-user-plus mr-3"></i>
                                {sidebarOpen && <span>Cadastre-se</span>}
                            </Link>
                        </div>

                        {sidebarOpen && (
                            <div className="mt-8 pt-4 border-t border-green-700 px-2">
                                <div className="p-2 text-sm text-green-200">
                                    Acesse o sistema para gerenciar as escolas do estado do Acre.
                                </div>
                            </div>
                        )}
                    </nav>
                </div>

                {/* Main Content Area */}
                <div className={`flex-1 ${sidebarOpen ? 'ml-64' : 'ml-20'} transition-all duration-300 flex flex-col min-h-screen`}>
                    {/* Conteúdo Central (irá crescer e empurrar o footer para baixo) */}
                    <main className="flex-grow flex items-center justify-center p-4 sm:p-6 lg:p-8">
                        <div className="w-full max-w-md">
                            {children}
                        </div>
                    </main>

                    {/* Footer (sempre na base) */}
                    <footer className="bg-gray-800 text-white">
                        <div className="max-w-7xl mx-auto py-4 px-4 sm:px-6 lg:px-8">
                            <div className="flex flex-col md:flex-row justify-between items-center">
                                <div className="mb-2 md:mb-0">
                                    <p className="text-sm">&copy; {new Date().getFullYear()} Secretaria de Educação do Acre. Todos os direitos reservados.</p>
                                </div>
                                <div className="flex space-x-4">
                                    <Link href="#" className="text-gray-300 hover:text-white text-sm">Termos de Uso</Link>
                                    <Link href="#" className="text-gray-300 hover:text-white text-sm">Contato</Link>
                                    <Link href="#" className="text-gray-300 hover:text-white text-sm">Ajuda</Link>
                                </div>
                            </div>
                        </div>
                    </footer>
                </div>
            </div>
        </div>
    );
}