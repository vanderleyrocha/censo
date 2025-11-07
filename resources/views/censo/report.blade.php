@include('censo.report-styles')

<h1>Relatório de Processamento - Censo Escolar 2025</h1>

<div class='summary'>
    <strong>Data de geração:</strong> {{ $currentDate }}<br>
    <strong>Total de arquivos com erro:</strong> {{ $totalErrors }}
</div>

<div class='success-summary'>
    <strong>Processamento Concluído com Sucesso:</strong><br>
    <strong>Total de escolas processadas:</strong> {{ $totalSchools }}<br>
    <strong>Total de registros importados:</strong> {{ $totalRecords }}
</div>

@if (!empty($schoolsByMunicipio))
    <h2>Escolas Processadas com Sucesso</h2>

    @php
        $municipioSummary = [];
    @endphp

    @foreach ($schoolsByMunicipio as $municipio => $schools)
        <div class='municipio-section'>
            <h3>Município: {{ $municipio }}</h3>
            <table>
                <thead>
                    <tr>
                        <th>Nome da Escola</th>
                        <th>Código INEP</th>
                        <th>Registros Importados</th>
                        <th>Status</th>
                    </tr>
                </thead>
                <tbody>
                    @php
                        $municipioTotalSchools = 0;
                        $municipioTotalRecords = 0;
                    @endphp

                    @foreach ($schools as $school)
                        @php
                            $municipioTotalSchools++;
                            $municipioTotalRecords += $school['registros_importados'];
                        @endphp
                        <tr>
                            <td>{{ $school['nome_escola'] }}</td>
                            <td>{{ $school['cod_inep_escola'] }}</td>
                            <td>{{ $school['registros_importados'] }}</td>
                            <td>
                                @if ($school['nova'])
                                    <span class="school-status-new">NOVA</span>
                                @else
                                    <span class="school-status-found">ENCONTRADA</span>
                                @endif
                            </td>
                        </tr>
                    @endforeach

                    <!-- Total por município -->
                    <tr class="total-row">
                        <td colspan="2"><strong>TOTAL DO MUNICÍPIO</strong></td>
                        <td><strong>{{ $municipioTotalRecords }}</strong></td>
                        <td><strong>{{ $municipioTotalSchools }} escola(s)</strong></td>
                    </tr>
                </tbody>
            </table>
        </div>

        @php
            $municipioSummary[$municipio] = [
                'total_escolas' => $municipioTotalSchools,
                'total_registros' => $municipioTotalRecords,
            ];
        @endphp
    @endforeach

    <!-- Tabela resumo por município -->
    <h2>Resumo por Município</h2>
    <table class="summary-table">
        <thead>
            <tr>
                <th>Nome do Município</th>
                <th>Total de Escolas Importadas</th>
                <th>Total de Registros Importados</th>
            </tr>
        </thead>
        <tbody>
            @php
                $grandTotalSchools = 0;
                $grandTotalRecords = 0;
            @endphp

            @foreach ($municipioSummary as $municipio => $totals)
                @php
                    $grandTotalSchools += $totals['total_escolas'];
                    $grandTotalRecords += $totals['total_registros'];
                @endphp
                <tr>
                    <td>{{ $municipio }}</td>
                    <td>{{ $totals['total_escolas'] }}</td>
                    <td>{{ $totals['total_registros'] }}</td>
                </tr>
            @endforeach

            <!-- Total geral -->
            <tr class="total-row">
                <td><strong>TOTAL GERAL</strong></td>
                <td><strong>{{ $grandTotalSchools }}</strong></td>
                <td><strong>{{ $grandTotalRecords }}</strong></td>
            </tr>
        </tbody>
    </table>
@endif

<!-- Tabela de escolas não encontradas -->
@if (!empty($missingSchools))
    <h2>Escolas Não Encontradas no Processamento</h2>
    <table class="missing-schools-table">
        <thead>
            <tr>
                <th>Município</th>
                <th>ID</th>
                <th>Nome</th>
                <th>Dependência</th>
                <th>Situação</th>
                <th>Zona</th>
                <th>Tipo Localização</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($missingSchools as $school)
                <tr>
                    <td>{{ $school->municipio }}</td>
                    <td>{{ $school->id }}</td>
                    <td>{{ $school->nome }}</td>
                    <td>{{ $school->dependencia }}</td>
                    <td>{{ $school->situacao }}</td>
                    <td>{{ $school->zona }}</td>
                    <td>{{ $school->tipo_localizacao }}</td>
                </tr>
            @endforeach
        </tbody>
    </table>
@endif

@if (!empty($reports))
    <h2>Arquivos com Erros no Processamento</h2>

    @foreach ($reports as $report)
        <div class='error-file'>
            <strong>Arquivo:</strong> {{ $report['file'] }}<br>
            <strong>Data do erro:</strong> {{ $report['timestamp']->format('d/m/Y H:i:s') }}<br>
            <strong>Erros encontrados:</strong>

            @foreach ($report['errors'] as $error)
                <div class='error-list'>• {{ $error }}</div>
            @endforeach
        </div>
    @endforeach
@endif

</body>

</html>
