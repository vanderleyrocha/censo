<!DOCTYPE html>
<html>

<head>
    <meta charset='UTF-8'>
    <title>Relatório de Processamento - Censo 2025</title>
    <style>
        body {
            font-family: Arial, sans-serif;
            margin: 20px;
        }

        h1 {
            color: #2c3e50;
        }

        h2 {
            color: #34495e;
            border-bottom: 2px solid #3498db;
            padding-bottom: 5px;
        }

        h3 {
            color: #2980b9;
            margin-top: 20px;
        }

        .error-file {
            background: #ffeaa7;
            padding: 10px;
            margin: 10px 0;
            border-left: 4px solid #e17055;
        }

        .error-list {
            background: #fab1a0;
            padding: 10px;
            margin: 5px 0;
        }

        .summary {
            background: #74b9ff;
            color: white;
            padding: 15px;
            margin: 20px 0;
        }

        .success-summary {
            background: #00b894;
            color: white;
            padding: 15px;
            margin: 20px 0;
        }

        table {
            width: 100%;
            border-collapse: collapse;
            margin: 15px 0;
        }

        th,
        td {
            border: 1px solid #ddd;
            padding: 8px;
            text-align: left;
        }

        th {
            background-color: #3498db;
            color: white;
        }

        tr:nth-child(even) {
            background-color: #f2f2f2;
        }

        .municipio-section {
            margin-bottom: 30px;
        }

        .school-status-new {
            color: #27ae60;
            font-weight: bold;
        }

        .school-status-found {
            color: #2980b9;
            font-weight: bold;
        }

        .total-row {
            background-color: #2c3e50;
            color: white;
            font-weight: bold;
        }

        .summary-table {
            background-color: #ecf0f1;
            margin: 20px 0;
        }

        .missing-schools-table {
            background-color: #fff3cd;
            margin: 20px 0;
        }
    </style>
</head>

<body>
