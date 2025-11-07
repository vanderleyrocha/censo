<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Cidade extends Model
{
    protected $fillable = [
        'nome',
        'estado_id',
        'regional_id',
        'ibge',
        'created_at',
        'updated_at'
    ];

    protected $casts = [
        'estado_id' => 'integer',
        'regional_id' => 'integer',
        'ibge' => 'integer',
    ];

    public function estado(): BelongsTo
    {
        return $this->belongsTo(Estado::class);
    }

    public function regional(): BelongsTo
    {
        return $this->belongsTo(Regional::class);
    }

    public function getIbgeFormatadoAttribute()
    {
        if (!$this->ibge || strlen((string)$this->ibge) !== 7) {
            return $this->ibge;
        }

        $ibgeString = str_pad((string)$this->ibge, 7, '0', STR_PAD_LEFT);
        $estado = substr($ibgeString, 0, 2);
        $municipio = substr($ibgeString, 2, 4);
        $digito = substr($ibgeString, 6, 1);

        return "$estado.$municipio-$digito";
    }
}
