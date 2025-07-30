/*
* As classes a seguir são usadas dinamicamente e precisam ser incluídas pelo Tailwind:
* bg-green-700
* bg-green-800
* bg-red-700
* bg-blue-700
*/

import { Link, usePage } from '@inertiajs/react';
import { useState, useEffect } from 'react';
import Dropdown from '@/Components/Dropdown';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import FlashMessage from '@/Components/FlashMessage';

export default function AuthenticatedLayout({ header, children, headerTitle }) {
    const { props } = usePage();
    const user = props.auth.user;
    const [mobileMenuOpen, setMobileMenuOpen] = useState(false);
    const [sidebarOpen, setSidebarOpen] = useState(true);
    const [accessControlOpen, setAccessControlOpen] = useState(true);

    const { flash } = usePage().props;
    const [flashMessage, setFlashMessage] = useState(flash?.success || null);
    const [flashError, setFlashError] = useState(flash?.error || null);

    const isAdmin = user?.roles.includes('system-admin');

    // Atualiza mensagens quando as props mudam
    useEffect(() => {
        setFlashMessage(props.flash?.success || null);
        setFlashError(props.flash?.error || null);
    }, [props.flash]);

    // Auto-fechamento das mensagens após 5 segundos
    useEffect(() => {
        const timer = setTimeout(() => {
            setFlashMessage(null);
            setFlashError(null);
        }, 10000);
        return () => clearTimeout(timer);
    }, [flashMessage, flashError]);

    return (
        <div className="min-h-screen bg-gray-50 flex flex-col">

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
                        className="text-green-200 hover:text-white focus:outline-none"
                    >
                        {sidebarOpen ? (<i className="fas fa-chevron-left"></i>) : (<i className="fas fa-chevron-right"></i>)}
                    </button>
                </div>

                {/* Mobile menu button */}
                <div className="sm:hidden p-4">
                    <button
                        onClick={() => setMobileMenuOpen(!mobileMenuOpen)}
                        className="text-white hover:text-green-200 focus:outline-none"
                    >
                        <i className={`fas ${mobileMenuOpen ? 'fa-times' : 'fa-bars'}`}></i>
                    </button>
                </div>

                {/* Desktop Navigation */}
                <nav className="hidden sm:block mt-4">
                    <div className="space-y-1 px-2">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                            className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                            activeClassName="bg-green-900"
                        >
                            <i className="fas fa-home mr-3"></i>
                            {sidebarOpen && <span>Início</span>}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            href={route('regioes.index')}
                            active={route().current('regioes.*')}
                            className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                            activeClassName="bg-green-900"
                        >
                            <i className="fas fa-map-marked-alt mr-3"></i>
                            {sidebarOpen && <span>Regiões</span>}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            href={route('regionais.index')}
                            active={route().current('regionais.*')}
                            className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                            activeClassName="bg-green-900"
                        >
                            <i className="fas fa-map-marked-alt mr-3"></i>
                            {sidebarOpen && <span>Regionais</span>}
                        </ResponsiveNavLink>

                        <ResponsiveNavLink
                            href={route('cidades.index')}
                            active={route().current('cidades.*')}
                            className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                            activeClassName="bg-green-900"
                        >
                            <i className="fas fa-city mr-3"></i>
                            {sidebarOpen && <span>Cidades</span>}
                        </ResponsiveNavLink>

                        {isAdmin && (
                            <>
                                <div className="pt-2 mt-2 border-t border-green-700">
                                    <p className="px-3 py-1 text-xs text-green-200 uppercase tracking-wider">
                                        {sidebarOpen && 'Administração'}
                                    </p>
                                </div>

                                <div className="group">
                                    <div
                                        className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200 cursor-pointer"
                                        onClick={() => setAccessControlOpen(!accessControlOpen)}
                                    >
                                        <i className="fas fa-lock mr-3"></i>
                                        {sidebarOpen && <span>Controle de acesso</span>}
                                        <i className={`fas fa-chevron-${accessControlOpen ? 'down' : 'right'} ml-auto text-xs ${!sidebarOpen && 'hidden'}`}></i>
                                    </div>

                                    {accessControlOpen && (
                                        <div className="pl-8 space-y-1">
                                            <ResponsiveNavLink
                                                href={route('roles.index')}
                                                active={route().current('roles.*')}
                                                className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-user-shield mr-3"></i>
                                                {sidebarOpen && <span>Funções</span>}
                                            </ResponsiveNavLink>

                                            <ResponsiveNavLink
                                                href={route('permissions')}
                                                active={route().current('permissions')}
                                                className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-key mr-3"></i>
                                                {sidebarOpen && <span>Permissões</span>}
                                            </ResponsiveNavLink>

                                            <ResponsiveNavLink
                                                href={route('users.index')}
                                                active={route().current('users.*')}
                                                className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-users mr-3"></i>
                                                {sidebarOpen && <span>Usuários</span>}
                                            </ResponsiveNavLink>
                                        </div>
                                    )}
                                </div>
                            </>
                        )}

                        <>
                            <div className="pt-2 mt-2 border-t border-green-700">
                                <p className="px-3 py-1 text-xs text-green-200 uppercase tracking-wider">
                                    {sidebarOpen && 'ESCOLAS'}
                                </p>
                            </div>

                            <ResponsiveNavLink
                                href={route('escolas.index')}
                                active={route().current('escolas.index')}
                                className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                                activeClassName="bg-green-900"
                            >
                                <i className="fas fa-school mr-3"></i>
                                {sidebarOpen && <span>Ver lista</span>}
                            </ResponsiveNavLink>

                            <ResponsiveNavLink
                                href={route('escolas.atribuir')}
                                active={route().current('escolas.atribuir')}
                                className="flex items-center p-2 rounded hover:bg-green-700 text-white transition-colors duration-200"
                                activeClassName="bg-green-900"
                            >
                                <i className="fas fa-user-tag mr-3"></i>
                                {sidebarOpen && <span>Atribuir Responsável</span>}
                            </ResponsiveNavLink>
                        </>
                    </div>

                    {/* User dropdown */}
                    <div className="mt-8 pt-4 border-t border-green-700 px-2">
                        <Dropdown>
                            <Dropdown.Trigger>
                                <div className="flex items-center p-2 rounded hover:bg-green-700 cursor-pointer transition-colors duration-200">
                                    <div className="h-8 w-8 rounded-full bg-green-600 flex items-center justify-center">
                                        <i className="fas fa-user text-white"></i>
                                    </div>
                                    {sidebarOpen && (
                                        <div className="ml-3 overflow-hidden">
                                            <p className="text-sm font-medium text-white truncate">{user.name}</p>
                                            <p className="text-xs text-green-200 truncate">{user.email}</p>
                                        </div>
                                    )}
                                </div>
                            </Dropdown.Trigger>

                            <Dropdown.Content position="right">
                                <Dropdown.Link href={route('profile.edit')}>
                                    <i className="fas fa-user mr-2"></i> Perfil
                                </Dropdown.Link>
                                <Dropdown.Link
                                    href={route('logout')}
                                    method="post"
                                    as="button"
                                    className="text-red-600 hover:bg-red-50"
                                >
                                    <i className="fas fa-sign-out-alt mr-2"></i> Sair
                                </Dropdown.Link>
                            </Dropdown.Content>
                        </Dropdown>
                    </div>
                </nav>

                {/* Mobile Navigation */}
                {mobileMenuOpen && (
                    <div className="sm:hidden mt-2 space-y-1 px-2">
                        <ResponsiveNavLink
                            href={route('dashboard')}
                            active={route().current('dashboard')}
                            className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-green-800"
                            activeClassName="bg-green-900"
                        >
                            Início
                        </ResponsiveNavLink>

                        {isAdmin && (
                            <>
                                <div className="pt-2 mt-2 border-t border-green-700">
                                    <p className="px-3 py-1 text-xs text-green-200 uppercase tracking-wider">
                                        Administração
                                    </p>
                                </div>

                                <div className="pl-2">
                                    <div
                                        className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-green-700 cursor-pointer"
                                        onClick={() => setAccessControlOpen(!accessControlOpen)}
                                    >
                                        <i className="fas fa-lock mr-2"></i> Controle de acesso
                                        <i className={`fas fa-chevron-${accessControlOpen ? 'down' : 'right'} ml-2 text-xs`}></i>
                                    </div>

                                    {accessControlOpen && (
                                        <div className="pl-4 space-y-1">
                                            <ResponsiveNavLink
                                                href={route('roles.index')}
                                                active={route().current('roles.*')}
                                                className="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-green-700"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-user-shield mr-2"></i> Funções
                                            </ResponsiveNavLink>

                                            <ResponsiveNavLink
                                                href={route('permissions')}
                                                active={route().current('permissions')}
                                                className="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-green-700"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-key mr-2"></i> Permissões
                                            </ResponsiveNavLink>

                                            <ResponsiveNavLink
                                                href={route('users.index')}
                                                active={route().current('users.*')}
                                                className="block px-3 py-2 rounded-md text-sm font-medium text-white hover:bg-green-700"
                                                activeClassName="bg-green-900"
                                            >
                                                <i className="fas fa-users mr-2"></i> Usuários
                                            </ResponsiveNavLink>
                                        </div>
                                    )}
                                </div>
                            </>
                        )}

                        <ResponsiveNavLink
                            href="#"
                            active={route().current('escolas')}
                            className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-green-700"
                            activeClassName="bg-green-900"
                        >
                            Escolas
                        </ResponsiveNavLink>
                        <ResponsiveNavLink
                            href={route('escolas.atribuir')}
                            active={route().current('escolas.atribuir')}
                            className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-green-700"
                            activeClassName="bg-green-900"
                        >
                            Atribuir Responsável
                        </ResponsiveNavLink>
                        <div className="border-t border-green-900 pt-2 mt-2">
                            <ResponsiveNavLink
                                href={route('profile.edit')}
                                className="block px-3 py-2 rounded-md text-base font-medium text-white hover:bg-green-700"
                            >
                                Perfil
                            </ResponsiveNavLink>
                            <ResponsiveNavLink
                                method="post"
                                href={route('logout')}
                                as="button"
                                className="block px-3 py-2 rounded-md text-base font-medium text-red-200 hover:bg-red-600 hover:text-white"
                            >
                                Sair
                            </ResponsiveNavLink>
                        </div>
                    </div>
                )}
            </div>

            {/* Main Content */}
            <div className={`flex-1 flex flex-col ${sidebarOpen ? 'ml-64' : 'ml-20'} transition-all duration-300`}>
                {/* Header */}
                <header className="bg-green-800 text-white shadow">
                    <div className="max-w-7xl mx-auto px-4 py-4 sm:px-6 lg:px-8 flex justify-between items-center">
                        <h2 className="text-xl font-semibold leading-tight">
                            {headerTitle || 'Painel de Controle'}
                        </h2>
                        {header && (
                            <div className="flex items-center space-x-4">
                                {header}
                            </div>
                        )}
                    </div>
                </header>

                <main className="flex-1 pb-8 px-4 sm:px-6 lg:px-8">
                    {/* Flash Messages */}
                    <div className="max-w-7xl mx-auto mt-4">
                        <FlashMessage
                            message={flashMessage}
                            type="success"
                            onDismiss={() => setFlashMessage(null)}
                        />
                        <FlashMessage
                            message={flashError}
                            type="error"
                            onDismiss={() => setFlashError(null)}
                        />
                    </div>
                    {children}
                </main>

                <footer className="bg-gray-800 text-white">
                    <div className="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                        <div className="flex flex-col md:flex-row justify-between items-center">
                            <div className="mb-4 md:mb-0">
                                <p>&copy; {new Date().getFullYear()} Secretaria de Educação do Acre. Todos os direitos reservados.</p>
                            </div>
                            <div className="flex space-x-6">
                                <Link href="#" className="text-gray-300 hover:text-white transition-colors duration-200">Termos de Uso</Link>
                                <Link href="#" className="text-gray-300 hover:text-white transition-colors duration-200">Contato</Link>
                                <Link href="#" className="text-gray-300 hover:text-white transition-colors duration-200">Ajuda</Link>
                            </div>
                        </div>
                    </div>
                </footer>
            </div>
        </div>
    );
}