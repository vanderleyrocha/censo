<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Escola extends Model
{
    protected $fillable = [
        'nome',
        'endereco',
        'bairro',
        'zona',
        'cidade_id',
        'dependencia',
        'situacao',
        'regulamentacao',
        'tipo_localizacao',
        'modalidade',
        'portaria',
        'ano_adesao',
        'email',
        'atualizado',
        'responsavel_censo',
        't_aluno_2024',
        'alunos_censo_2024',
        'alunos_simaed',
        'total_registros_importados_2025',
        'nova',
        'encontrada',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'atualizado' => 'boolean',
        'encontrada' => 'boolean',
        't_aluno_2024' => 'integer',
        'alunos_censo_2024' => 'integer',
        'alunos_simaed' => 'integer',
        'total_registros_importados_2025' => 'integer',
        'ano_adesao' => 'integer',
    ];

    public function cidade(): BelongsTo
    {
        return $this->belongsTo(Cidade::class);
    }

    public function responsavel(): BelongsTo
    {
        return $this->belongsTo(Servidor::class, 'responsavel_censo', 'id');
    }

    public function alunos()
    {
        return $this->hasMany(Aluno::class, 'cod_inep_escola', 'id');
    }

    public function alunosSimaed()
    {
        return $this->hasMany(AlunoSimaed::class, 'censo', 'id');
    }
}