<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Cliente;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\ConfigCaixa;
use App\Models\DivisaoGrade;
use App\Models\Empresa;
use App\Models\Estoque;
use Illuminate\Http\Request;

class ProdutoController extends Controller
{
    public function getBarcode()
    {
        try {
            $rand = rand(11111, 99999);
            $code = $this->incluiDigito('7891000' . $rand);
            return response()->json($code, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    private function incluiDigito($code)
    {
        $weightflag = true;
        $sum = 0;
        for ($i = strlen($code) - 1; $i >= 0; $i--) {
            $sum += (int)$code[$i] * ($weightflag ? 3 : 1);
            $weightflag = !$weightflag;
        }
        return $code . (10 - ($sum % 10)) % 10;
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'valor_compra' =>  __convert_value_bd($request->valor_compra),
                'valor_venda' => __convert_value_bd($request->valor_venda),
                'referencia' => $request->referencia ?? '',
                'estoque_inicial' => $request->estoque_inicial ?? 0,
                'estoque_minimo' => $request->estoque_minimo ?? 0,
                'cor' => $request->cor ?? 0,
                'valor_livre' => $request->valor_livre ?? false,
                'cListServ' => $request->cListServ ?? '',
                'descricao_anp' => $request->descricao_anp ?? '',
                'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
                'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'CEST' => $request->CEST ?? '',
                'referencia_balanca' => $request->referencia_balanca ?? 0,
                'perc_comissao' => $request->perc_comissao ?? 0,
                'tipo_dimensao' => $request->tipo_dimensao ?? '',
                'perc_glp' => $request->perc_glp ?? 0,
                'perc_gnn' => $request->perc_gnn ?? 0,
                'perc_gni' => $request->perc_gni ?? 0,
                'perc_reducao' => $request->perc_reducao ?? 0,
                'valor_partida' => $request->valor_partida ?? 0,
                'unidade_tributavel' => $request->unidade_tributavel ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
                'largura' => $request->largura ?? 0,
                'altura' => $request->altura ?? 0,
                'comprimento' => $request->comprimento ?? 0,
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'lote' => $request->lote ?? 0,
                'vencimento' => $request->vencimento ?? '',
                'renavam' => $request->renavam ?? '',
                'placa' => $request->placa ?? '',
                'chassi' => $request->chassi ?? '',
                'combustivel' => $request->combustivel ?? '',
                'ano_modelo' => $request->ano_modelo ?? '',
                'cor_veiculo' => $request->cor_veiculo ?? '',
                'perc_ipi' => $request->perc_ipi ?? 0,
                'codBarras' => $request->codBarras ?? 0,
                'perc_iss' => $request->perc_iss ?? 0,
                'conversao_unitaria' => $request->conversao_unitaria ?? 1,
                'cBenef' => $request->cBenef ?? 0,
                'imagem' => '',
                'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'unidade_compra' => 'UN',
                'unidade_venda' => 'UN',
                'valor_locacao' => $request->valor_locacao ?? 0,
                'CFOP_entrada_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_estadual,
                'CFOP_entrada_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_inter_estadual,
            ]);

            $item = Produto::create($request->all());

            if ($request->estoque_inicial > 0) {
                // Estoque::create([
                //     'empresa_id' => $request->empresa_id,
                //     'produto_id' => $item->id,
                //     'quantidade' => $request->estoque_inicial,
                //     'valor_compra' => $request->valor_compra,
                // ]);
            }
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function pesquisa(Request $request)
    {
        $filial_id = $request->filial_id ?? null;

        $data = Produto::orderBy('nome', 'desc')
        ->select('produtos.*')
            // ->join('estoques', 'produto_id', '=', 'estoques.produto_id')
        ->where('produtos.empresa_id', $request->empresa_id)
        ->where('produtos.nome', 'like', "%$request->pesquisa%")
        ->with('estoque')
        ->get();
        $permissaoAcesso = __getLocaisUsarioLogado($request->usuario_id);
        $temp = [];
        foreach ($data as $p) {
            $locais = json_decode($p->locais);
            $p->estoqueAtual = $p->estoquePorLocalPavaVenda($filial_id);
            if($filial_id){
                foreach ($locais as $l) {
                    if ($l == $filial_id) {
                        array_push($temp, $p);
                    }
                }
            }else{
                array_push($temp, $p);
            }
        }
        return response()->json($temp, 200);
    }

    public function find($id)
    {
        $item = Produto::with('estoque')
        ->where('id', $id)
        ->first();
        return response()->json($item, 200);
    }

    public function findByBarcode(Request $request)
    {
        $item = Produto::with('estoque')
        ->where('codBarras', $request->barcode)
        ->where('empresa_id', $request->empresa_id)
        ->first();
        return response()->json($item, 200);
    }

    public function findByBarcodeReference(Request $request)
    {
        $config = ConfigCaixa::where('usuario_id', $request->usuario_id)
        ->first();

        $balanca_valor_peso = $config != null ? $config->balanca_valor_peso : 0;
        $balanca_digito_verificador = $config != null ? $config->balanca_digito_verificador : 6;
        $barcode = $request->barcode;
        $ref = (int)substr($barcode, 1, $balanca_digito_verificador);
        $valor = (float)substr($barcode, 7, 12);
        $valor = $valor / 1000;
        $quantidade = 1;

        $item = Produto::with('estoque')
        ->where('referencia_balanca', $ref)
        ->where('empresa_id', $request->empresa_id)
        ->first();

        if ($item->unidade_venda == 'KG') {
            if ($balanca_valor_peso == 1) {
                $quantidade = $valor / $item->valor_venda;
                $valor = $valor;
            } else {
                $quantidade = $valor / 10;
                $valor = $item->valor_venda * $quantidade;
            }
        }
        if ($item) {
            $item->valor = $valor;
            $item->quantidade = $quantidade;
        }

        return response()->json($item, 200);
    }

    public function linhaProdutoCompra(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;

            $product = Produto::findOrFail($product_id);
            $rand = rand(0, 10000000);
            return view('compra_manual.partials.row_product_purchase', 
                compact('product', 'qtd', 'value_unit', 'sub_total', 'rand'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function linhaParcelaCompra(Request $request)
    {
        try {
            $vencimento = $request->vencimento;
            $valor_parcela = $request->valor_parcela;

            return view('compra_manual.partials.row_payment_purchase', compact('valor_parcela', 'vencimento'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function storeProdutoRapido(Request $request)
    {
        try {

            $request->merge([
                'valor_compra' =>  __convert_value_bd($request->valor_compra),
                'valor_venda' => __convert_value_bd($request->valor_venda),
                'referencia' => $request->referencia ?? '',
                'estoque_inicial' => $request->estoque_inicial ?? 0,
                'estoque_minimo' => $request->estoque_minimo ?? 0,
                'cor' => $request->cor ?? 0,
                'valor_livre' => $request->valor_livre ?? false,
                'cListServ' => $request->cListServ ?? '',
                'descricao_anp' => $request->descricao_anp ?? '',
                'info_tecnica_composto' => $request->info_tecnica_composto ?? '',
                'limite_maximo_desconto' => $request->limite_maximo_desconto ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'CEST' => $request->CEST ?? 0,
                'referencia_balanca' => $request->referencia_balanca ?? 0,
                'perc_comissao' => $request->perc_comissao ?? 0,
                'tipo_dimensao' => $request->tipo_dimensao ?? '',
                'perc_glp' => $request->perc_glp ?? 0,
                'perc_gnn' => $request->perc_gnn ?? 0,
                'perc_gni' => $request->perc_gni ?? 0,
                'valor_partida' => $request->valor_partida ?? 0,
                'unidade_tributavel' => $request->unidade_tributavel ?? '',
                'quantidade_tributavel' => $request->quantidade_tributavel ?? 0,
                'largura' => $request->largura ?? 0,
                'altura' => $request->altura ?? 0,
                'comprimento' => $request->comprimento ?? 0,
                'peso_liquido' => $request->peso_liquido ?? 0,
                'peso_bruto' => $request->peso_bruto ?? 0,
                'lote' => $request->lote ?? 0,
                'vencimento' => $request->vencimento ?? '',
                'renavam' => $request->renavam ?? '',
                'placa' => $request->placa ?? '',
                'chassi' => $request->chassi ?? '',
                'combustivel' => $request->combustivel ?? '',
                'ano_modelo' => $request->ano_modelo ?? '',
                'cor_veiculo' => $request->cor_veiculo ?? '',
                'perc_ipi' => $request->perc_ipi ?? 0,
                'codBarras' => $request->codBarras ?? 0,
                'perc_iss' => $request->perc_iss ?? 0,
                'cBenef' => $request->cBenef ?? 0,
                'tela_pedido_id' => $request->tela_pedido_id != "" ? $request->tela_pedido_id : 0,
                'imagem' => '',
                'perc_icms_interestadual' => $request->perc_icms_interestadual ?? 0,
                'perc_icms_interno' => $request->perc_icms_interno ?? 0,
                'perc_fcp_interestadual' => $request->perc_fcp_interestadual ?? 0,
                'alerta_vencimento' => $request->alerta_vencimento ?? 0,
                'unidade_compra' => 'UN',
                'unidade_venda' => 'UN',
                'codigo_anp' => 0,
                'gerenciar_estoque' => 0,
                'CFOP_saida_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_saida_estadual,
                'CFOP_saida_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_saida_inter_estadual,
                'CFOP_entrada_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_estadual,
                'CFOP_entrada_inter_estadual' => NaturezaOperacao::where('empresa_id', $request->empresa_id)->first()->CFOP_entrada_inter_estadual,

            ]);
            $item = Produto::create($request->all());
            return response()->json($item, 200);
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 400);
        }
    }

    public function linhaProdutoReceita(Request $request)
    {
        try {
            $qtd = $request->qtd;
            $product_id = $request->product_id;

            $item = Produto::findOrFail($product_id);
            return view('produtos.produtos_composto._row_product_receita', compact('item', 'qtd'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function montarGrade(Request $request)
    {
        $comb = $request->divisoes;
        $sub = $request->subDivisoes;

        return view('produtos.partials._grade', compact('comb', 'sub'));
    }

    public function findProdRemessa(Request $request)
    {
        $cliente = null;
        if (isset($request->cliente_id)) {
            $cliente = Cliente::find($request->cliente_id);
        }
        $item = Produto::where('id', $request->produto_id)
        ->first();

        $item->cfop_atual = $item->cfop_estadual;

        if ($cliente != null) {

            $empresa = Empresa::find($item->empresa_id);
            if ($empresa != null) {

                if ($empresa->cidade && $empresa->cidade->uf != $cliente->cidade->uf) {
                    $item->cfop_atual = $item->cfop_outro_estado;
                }
            }
        }
        return response()->json($item, 200);
    }
}
