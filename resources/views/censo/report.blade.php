<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <title>Relatório de Processamento - Censo 2025</title>
    @include('censo.report-styles')
</head>

<body>

    <div class="header">
        <h1>Relatório de Processamento - Censo Escolar 2025</h1>
        <p>Data de geração: {{ $currentDate ?? now()->format('d/m/Y H:i:s') }}</p>
    </div>

    <div class="section">
        <h2>Resumo do Processamento</h2>
        <table class="tabela-resumo">
            <tr>
                <th>Total de arquivos com erro</th>
                <td class="tabela-totais">{{ number_format($totalErrors ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total de escolas processadas</th>
                <td class="tabela-totais">{{ number_format($totalSchools ?? 0, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <th>Total de registros importados</th>
                <td class="tabela-totais">{{ number_format($totalRecords ?? 0, 0, ',', '.') }}</td>
            </tr>
        </table>
    </div>

    @if (!empty($schoolsByMunicipio))
        @foreach ($schoolsByMunicipio as $municipio => $schools)
            <div class="section">
                <h2>Município: {{ $municipio }}</h2>
                <table class="tabela-municipio">
                    <thead>
                        <tr>
                            <th>Código INEP</th>
                            <th>Nome da Escola</th>
                            <th>Status</th>
                            <th class="tabela-totais">Registros Importados</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($schools as $school)
                            <tr>
                                <td>{{ $school['cod_inep_escola'] }}</td>
                                <td>{{ $school['nome_escola'] }}</td>
                                <td>{{ $school['nova'] ? 'NOVA' : 'ENCONTRADA' }}</td>
                                <td class="tabela-totais">{{ number_format($school['registros_importados'] ?? 0, 0, ',', '.') }}</td>
                            </tr>
                        @endforeach
                        <tr class="subtotal">
                            <td colspan="3"><strong>Total de escolas no município:</strong></td>
                            <td><strong>{{ count($schools) }}</strong></td>
                        </tr>
                        <tr class="subtotal">
                            <td colspan="3"><strong>Total de registros importados:</strong></td>
                            <td class="tabela-totais"><strong>{{ number_format(array_sum(array_column($schools, 'registros_importados')), 0, ',', '.') }}</strong>
                            </td>
                        </tr>
                    </tbody>
                </table>
            </div>
        @endforeach
    @endif

    @if (!empty($reports))
        <div class="section">
            <h2>Arquivos com Erro ({{ count($reports) }})</h2>
            @foreach ($reports as $report)
                <div class="error-section">
                    <h3>Arquivo: {{ $report['file'] }}</h3>
                    <p><strong>Data/Hora:</strong> {{ $report['timestamp']->format('d/m/Y H:i:s') }}</p>
                    <ul>
                        @foreach ($report['errors'] as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
                @if (!$loop->last)
                    <hr>
                @endif
            @endforeach
        </div>
    @endif

    @if (!empty($missingSchools))
        <div class="section">
            <h2>Escolas Não Encontradas no Processamento</h2>
            <table class="tabela-escolas-nao-encontradas">
                <thead>
                    <tr>
                        <th>Município</th>
                        <th>Código INEP</th>
                        <th>Nome</th>
                        <th>Dependência</th>
                        <th>Situação</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach ($missingSchools as $escola)
                        <tr>
                            <td>{{ $escola->municipio }}</td>
                            <td>{{ $escola->id }}</td>
                            <td>{{ $escola->nome }}</td>
                            <td>{{ $escola->dependencia }}</td>
                            <td>{{ $escola->situacao }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    @endif

</body>

</html>
