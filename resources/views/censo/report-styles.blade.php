{{-- resources/views/censo/report-styles.blade.php --}}
<style>
    body {
        font-family: "DejaVu Sans", Arial, Helvetica, sans-serif;
        font-size: 12px;
        color: #222;
        margin: 20px;
    }

    h1,
    h2 {
        text-align: center;
        color: #333;
    }

    .header {
        text-align: center;
        margin-bottom: 20px;
    }

    .section {
        margin-top: 25px;
        page-break-inside: avoid;
    }

    table {
        width: 100%;
        border-collapse: collapse;
        margin-top: 10px;
    }

    th,
    td {
        border: 1px solid #999;
        padding: 6px 8px;
        text-align: left;
        vertical-align: top;
    }

    th {
        background-color: #e8e8e8;
        font-weight: bold;
    }

    .subtotal {
        background-color: #f5f5f5;
    }

    .tabela-resumo,
    .tabela-totais {
        width: 50%;
        margin: 0 auto;
    }

    .tabela-resumo th,
    .tabela-totais th {
        width: 70%;
    }

    .tabela-resumo td,
    .tabela-totais td {
        text-align: right;
    }

    /* Nova configuração visual para a tabela de escolas não encontradas */
    .tabela-escolas-nao-encontradas {
        font-size: 10px;
        line-height: 1.2;
        width: 100%;
    }

    .tabela-escolas-nao-encontradas th,
    .tabela-escolas-nao-encontradas td {
        padding: 4px 6px;
        word-break: break-word;
    }

    .tabela-escolas-nao-encontradas th {
        background-color: #f0f0f0;
    }

    .error-section {
        margin-bottom: 15px;
        padding: 10px;
        background-color: #f8f8f8;
        border-left: 4px solid #e74c3c;
    }

    .error-section h3 {
        margin-top: 0;
        color: #c0392b;
    }

    .error-section ul {
        margin-bottom: 0;
    }

    hr {
        border: 0;
        border-top: 1px solid #ddd;
        margin: 15px 0;
    }

    /* Mantém o layout limpo no PDF */
    @page {
        margin: 20mm;
    }

    .page-break {
        page-break-before: always;
    }
</style>
