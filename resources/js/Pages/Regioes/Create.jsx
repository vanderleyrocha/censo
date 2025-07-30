// Create.jsx
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import Button from '@/Components/Button';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import SelectInput from '@/Components/SelectInput';
import InputError from '@/Components/InputError';

export default function Create({ servidores }) {
    const { data, setData, post, errors } = useForm({
        nome: '',
        sigla: '',
        servidor_id: ''
    });

    const submit = (e) => {
        e.preventDefault();
        post(route('regioes.store'));
    };

    return (
        <AuthenticatedLayout
            headerTitle="Criar Nova Região"
            headerActions={
                <Link href={route('regioes.index')}>
                    <Button color="secondary" size="sm">
                        <i className="fas fa-arrow-left mr-2"></i> Voltar
                    </Button>
                </Link>
            }
        >
            <Head title="Criar Nova Região" />

            <div className="max-w-3xl mx-auto bg-white p-6 rounded-lg shadow">
                <form onSubmit={submit}>
                    <div className="grid grid-cols-1 gap-6">
                        <div>
                            <InputLabel value="Nome" />
                            <TextInput
                                value={data.nome}
                                onChange={(e) => setData('nome', e.target.value)}
                                className="w-full mt-1"
                            />
                            <InputError message={errors.nome} />
                        </div>

                        <div>
                            <InputLabel value="Sigla" />
                            <TextInput
                                value={data.sigla}
                                onChange={(e) => setData('sigla', e.target.value)}
                                className="w-full mt-1"
                                maxLength="10"
                            />
                            <InputError message={errors.sigla} />
                        </div>

                        <div>
                            <InputLabel value="Servidor Responsável" />
                            <SelectInput
                                value={data.servidor_id}
                                onChange={(e) => setData('servidor_id', e.target.value)}
                                className="w-full mt-1"
                            >
                                <option value="">Selecione um servidor</option>
                                {servidores.map((servidor) => (
                                    <option key={servidor.id} value={servidor.id}>
                                        {servidor.nome}
                                    </option>
                                ))}
                            </SelectInput>
                            <InputError message={errors.servidor_id} />
                        </div>

                        <div className="flex justify-end">
                            <Button type="submit" color="primary">
                                <i className="fas fa-save mr-2"></i> Salvar
                            </Button>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}