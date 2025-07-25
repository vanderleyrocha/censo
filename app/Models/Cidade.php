<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Cidade extends Model
{
    public function regional()
    {
        return $this->belongsTo(Regional::class, 'regional_id');
    }
}
