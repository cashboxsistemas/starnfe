<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Venda;
use App\Models\VendaCaixa;
use App\Models\Produto;
use App\Models\ItemVendaCaixa;
use App\Models\ItemVenda;
use App\Models\ContaReceber;
use App\Models\ContaPagar;

class GraficoController extends Controller
{
    public function vendasAnual(Request $request){
        $meseStr = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $somaVendas = [];
        $meses = [];
        foreach($this->criaMeses() as $m){
            $vendas = Venda::
            where('empresa_id', $request->empresa_id)
            ->whereMonth('created_at', $m)
            ->sum('valor_total');

            $vendasCaixa = VendaCaixa::
            where('empresa_id', $request->empresa_id)
            ->whereMonth('created_at', $m)
            ->sum('valor_total');

            array_push($somaVendas, $vendas+$vendasCaixa);
            array_push($meses, $meseStr[(int)$m-1]);

        }

        $retorno = [
            'meses' => $meses,
            'somaVendas' => $somaVendas,
        ];

        return response()->json($retorno, 200);
    }

    private function criaMeses(){
        $mesAtual = date('m');
        $mesAtual = (int)$mesAtual;
        $meses = [];
        for($i=1; $i<=$mesAtual; $i++){
            array_push($meses, $i < 10 ? "0$i" : $i);
        }
        return $meses;
    }

    private function criaMesesTrimestre(){
        $mesAtual = date('m');
        $mesAtual = (int)$mesAtual;
        $meses = [];
        for($i=1; $i<=$mesAtual; $i++){
            array_push($meses, $i < 10 ? "0$i" : $i);
        }
        return $meses;
    }

    public function produtos(Request $request){
        $meseStr = ['Jan', 'Fev', 'Mar', 'Abr', 'Mai', 'Jun', 'Jul', 'Ago', 'Set', 'Out', 'Nov', 'Dez'];
        $somaCadastradoMes = [];
        $somaVendidosNoDia = [];
        $somaNaoVendidos = [];
        $meses = [];
        
        $filial_id = $request->filial_id;

        foreach($this->criaMeses() as $m){
            $countProdutos = Produto::
            where('empresa_id', $request->empresa_id)
            ->where('locais', 'like', "%{$filial_id}%")
            ->whereMonth('created_at', $m)
            ->count();
            array_push($somaCadastradoMes, $countProdutos);

            $caixa = ItemVendaCaixa::
            select('item_venda_caixas.*')
            ->join('venda_caixas', 'venda_caixas.id', '=', 'item_venda_caixas.venda_caixa_id')
            ->where('venda_caixas.empresa_id', $request->empresa_id)
            ->whereMonth('item_venda_caixas.created_at', $m)
            ->groupBy('item_venda_caixas.produto_id')
            ->get();

            $pedido = ItemVenda::
            select('item_vendas.*')
            ->join('vendas', 'vendas.id', '=', 'item_vendas.venda_id')
            ->where('vendas.empresa_id', $request->empresa_id)
            ->whereMonth('item_vendas.created_at', $m)
            ->groupBy('item_vendas.produto_id')
            ->get();

            $somaVendidos = 0;
            $somaVendidos = $this->somaProdutosDistintos($caixa, $pedido);
            array_push($somaVendidosNoDia, $somaVendidos);

            $caixa = \DB::table('produtos AS t1')
            ->select('t1.*')
            ->leftJoin('item_venda_caixas AS t2','t2.produto_id','=','t1.id')
            ->where('t1.empresa_id', $request->empresa_id)
            ->whereNull('t2.produto_id')->get();

            $pedido = \DB::table('produtos AS t1')
            ->select('t1.*')
            ->leftJoin('item_vendas AS t2','t2.produto_id','=','t1.id')
            ->where('t1.empresa_id', $request->empresa_id)
            ->whereNull('t2.produto_id')->get();

            $semVendas = 0;
            $semVendas = $this->somaProdutosNaoVendidos($caixa, $pedido);
            array_push($somaNaoVendidos, $semVendas);

            array_push($meses, $meseStr[(int)$m-1]);
        }

        $retorno = [
            'meses' => $meses,
            'somaCadastradoMes' => $somaCadastradoMes,
            'somaVendidosNoDia' => $somaVendidosNoDia,
            'somaNaoVendidos' => $somaNaoVendidos,
        ];

        return response()->json($retorno, 200);
    }

    private function somaProdutosDistintos($caixa, $pedido){
        $cont = sizeof($caixa);
        $ids = $caixa->pluck('produto_id')->toArray();
        foreach($pedido as $p){
            if(!in_array($p->produto_id, $ids)){
                $cont++;
            }
        }
        return $cont;
    }

    private function somaProdutosNaoVendidos($caixa, $pedido){
        $cont = sizeof($caixa);
        $ids = $caixa->pluck('id')->toArray();
        foreach($pedido as $p){
            if(!in_array($p->id, $ids)){
                $cont++;
            }
        }
        return $cont;
    }

    public function contasReceber(Request $request){
        $local_id = $request->filial_id;

        $recebidas = ContaReceber::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('status', 1)
        ->sum('valor_integral');

        $receber = ContaReceber::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('status', 0)
        ->sum('valor_integral');

        $sumTotal = ContaReceber::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->sum('valor_integral');

        $retorno = [
            'recebidas' => __moeda($recebidas),
            'receber' => __moeda($receber),
            'percentual' => $sumTotal > 0 ? number_format(($recebidas/$sumTotal)*100,0) : 0,
        ];
        return response()->json($retorno, 200);
    }

    public function contasPagar(Request $request){
        $local_id = $request->filial_id;
        $pagos = ContaPagar::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->where('status', 1)
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->sum('valor_integral');

        $pagar = ContaPagar::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->where('status', 0)
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->sum('valor_integral');

        $sumTotal = ContaPagar::
        where('empresa_id', $request->empresa_id)
        ->whereMonth('created_at', date('m'))
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->sum('valor_integral');

        $retorno = [
            'pagos' => __moeda($pagos),
            'pagar' => __moeda($pagar),
            'percentual' => $sumTotal > 0 ? number_format(($pagos/$sumTotal)*100,0) : 0,
        ];

        return response()->json($retorno, 200);
    }

    public function boxConsulta(Request $request){
        $dias = $request->dias;
        $data = [
            'totalDeVendas' => $this->totalDeVendasDias($dias, $request->empresa_id),
            'totalDeContaReceber' => $this->totalDeContaReceberDias($dias, $request->empresa_id),
            'totalDeContaPagar' => $this->totalDeContaPagarDias($dias, $request->empresa_id)
        ];

        return response()->json($data, 200);
    }

    private function totalDeContaPagarDias($dias, $empresa_id){
        $contas = ContaPagar::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeContaReceberDias($dias, $empresa_id){
        $contas = ContaReceber::
        select(\DB::raw('sum(valor_integral) as total'))
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('status', false)
        ->where('empresa_id', $empresa_id)
        ->first(); 

        return $contas->total ? number_format($contas->total, 2, ',', '.') : 0;
    }

    private function totalDeVendasDias($dias, $empresa_id){
        $vendas = Venda::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d')
        ])
        ->where('empresa_id', $empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-$dias days")), 
            date('Y-m-d', strtotime('+1 day'))
        ])
        ->where('empresa_id', $empresa_id)
        ->first();

        return number_format($vendas->total + $vendaCaixas->total, 2, ',', '.');

    }

    public function getDataCards(Request $request){
        $empresa_id = $request->empresa_id;
        $local_id = $request->local_id;
        $vendas = Venda::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-30 days")), 
            date('Y-m-d')
        ])
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('empresa_id', $empresa_id)
        ->first();

        $vendaCaixas = VendaCaixa::
        select(\DB::raw('sum(valor_total) as total'))
        ->whereBetween('created_at', [
            date('Y-m-d', strtotime("-30 days")), 
            date('Y-m-d')
        ])
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('empresa_id', $empresa_id)
        ->first();

        $countProdutos = Produto::
        where('empresa_id', $request->empresa_id)
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            return $query->where('locais', 'like', "%{$local_id}%");
        })
        ->count();

        $totalPagar = ContaPagar::
        where('empresa_id', $request->empresa_id)
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-15 days")), 
            date('Y-m-d', strtotime("+15 days"))
        ])
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('status', 0)
        ->sum('valor_integral');

        $totalReceber = ContaReceber::
        where('empresa_id', $request->empresa_id)
        ->whereBetween('data_vencimento', [
            date('Y-m-d', strtotime("-15 days")), 
            date('Y-m-d', strtotime("+15 days"))
        ])
        ->when($local_id != 'todos', function ($query) use ($local_id) {
            $local_id = $local_id == -1 ? null : $local_id;
            return $query->where('filial_id', $local_id);
        })
        ->where('status', 0)
        ->sum('valor_integral');

        $data = [
            'vendas' => $vendas->total + $vendaCaixas->total,
            'produtos' => $countProdutos,
            'conta_pagar' => $totalPagar,
            'conta_receber' => $totalReceber,
        ];
        return response()->json($data, 200);
    }
}
