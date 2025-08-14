<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ErroLog extends Model
{
    use HasFactory;

    protected $fillable = [ 'arquivo', 'linha', 'erro', 'empresa_id' ];

    public function empresa(){
        return $this->belongsTo(Empresa::class, 'empresa_id');
    }
}
