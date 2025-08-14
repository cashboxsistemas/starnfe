<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ContaReceber;
use App\Models\ContaPagar;
use App\Models\Produto;

class NotificacaoController extends Controller
{
    public function index(Request $request){
        $contaReceber = ContaReceber::
        where('empresa_id', $request->empresa_id)
        ->where('status', 0)
        ->whereDate('data_vencimento', '<=', date('Y-m-d'))
        ->sum('valor_integral');

        $contaPagar = ContaPagar::
        where('empresa_id', $request->empresa_id)
        ->where('status', 0)
        ->whereDate('data_vencimento', '<=', date('Y-m-d'))
        ->sum('valor_integral');

        $produtos = Produto::
        where('empresa_id', $request->empresa_id)
        ->where('estoque_minimo', '>', 0)
        ->get();

        $produtosComAlertaEstoque = [];
        foreach($produtos as $p){
            if($p->estoqueAtual2() < $p->estoque_minimo){
                $temp = [
                    'id' => $p->id,
                    'nome' => $p->nome,
                    'estoque' => $p->estoqueAtual(),
                ];
                array_push($produtosComAlertaEstoque, $temp);
            }
        }

        return view('notificacoes.index', 
            compact('contaReceber', 'contaPagar', 'produtosComAlertaEstoque'));
    }
}
