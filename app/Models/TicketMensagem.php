<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class TicketMensagem extends Model
{
    use HasFactory;
    protected $fillable = ['mensagem', 'imagem', 'ticket_id', 'usuario_id'];

    public function usuario(){
        return $this->belongsTo(Usuario::class, 'usuario_id');
    }

    public function mensagemSuper(){
    	if(isSuper($this->usuario->login)) return true;
    	return false;
    }
}
