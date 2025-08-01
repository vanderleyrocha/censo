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
        if (!$this->ibge || strlen($this->ibge) !== 7) {
            return $this->ibge;
        }

        $estado = substr($this->ibge, 0, 2);
        $municipio = substr($this->ibge, 2, 4);
        $digito = substr($this->ibge, 6, 1);

        return "$estado.$municipio-$digito";
    }
}
