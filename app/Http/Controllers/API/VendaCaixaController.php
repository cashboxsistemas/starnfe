<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Models\Empresa;
use App\Models\VendaCaixa;
use App\Helpers\StockMove;
use App\Models\CategoriaConta;
use App\Models\ItemVendaCaixa;
use App\Models\Funcionario;
use App\Models\ComissaoVenda;
use App\Models\ContaReceber;
use App\Models\FaturaFrenteCaixa;
use App\Models\Pedido;
use App\Models\VendaCaixaPreVenda;
use App\Models\Produto;

class VendaCaixaController extends Controller
{
    public function store(Request $request)
    {
        try {
            $vendaCaixa = DB::transaction(function () use ($request) {
                $valor_total = $this->somaItens($request);
                $empresa = Empresa::findOrFail($request->empresa_id);

                $prevenda = VendaCaixaPreVenda::find($request->prevenda_id);
                if ($prevenda != null) {
                    $prevenda->status = 1;
                    $prevenda->save();
                }

                $request->merge([
                    'usuario_id' => $request->usuario_id,
                    'observacao' => $request->observacao ?? '',
                    'qtd_volumes' => $request->qtd_volumes ?? 0,
                    'peso_liquido' => $request->peso_liquido ?? 0,
                    'peso_bruto' => $request->peso_bruto ?? 0,
                    'desconto' => $request->desconto ?? 0,
                    'valor_total' => $valor_total + $request->acrescimo - $request->desconto,
                    'estado_emissao' => 'novo',
                    'sequencia_cce' => $request->sequencia_cce ?? 0,
                    'chave' => $request->chave ?? 0,
                    'acrescimo' => $request->acrescimo ?? 0,
                    'natureza_id' => $empresa->configNota->nat_op_padrao,
                    'dinheiro_recebido' => $request->valor_recebido ? __convert_value_bd($request->valor_recebido) : 0,
                    'troco' => 0,
                    'forma_pagamento' => '',
                    'tipo_pagamento' => $request->tipo_pagamento_row ? '99' : $request->tipo_pagamento,
                    'estado' => 'novo',
                    'nome' => $request->nome,
                    'cpf' => $request->cpf_cnpj ?? '',
                    'pedido_delivery_id' => 0,
                    'qr_code_base64' => 0,
                    'cnpj_cartao' => $request->cnpj_cartao ?? '',
                    'bandeira_cartao' => $request->bandeira_cartao ?? '',
                    'cAut_cartao' => $request->cAut_cartao ?? '',
                    'filial_id' => $request->filial_id != -1 ? $request->filial_id : null
                ]);

                $vendaCaixa = VendaCaixa::create($request->all());
                $stockMove = new StockMove();
                for ($i = 0; $i < sizeof($request->produto_id); $i++) {
                    $product = Produto::findOrFail($request->produto_id[$i]);
                    $cfop = 0;
                    if ($empresa->configNota->natureza->sobrescreve_cfop) {
                        $cfop = $empresa->configNota->natureza->CFOP_saida_estadual;
                    } else {
                        $cfop = $product->CFOP_saida_estadual;
                    }
                    ItemVendaCaixa::create([
                        'venda_caixa_id' => $vendaCaixa->id,
                        'produto_id' => (int)$request->produto_id[$i],
                        'quantidade' => __convert_value_bd($request->quantidade[$i]),
                        'valor' => __convert_value_bd($request->valor_unitario[$i]),
                        'valor_custo' => $product->valor_compra,
                        'cfop' => $cfop,
                        'observacao' => $request->observacao ?? '',
                        'item_pedido_id' => null
                    ]);
                    $stockMove->downStock(
                        $product->id,
                        __convert_value_bd($request->quantidade[$i]),
                        $request->filial_id
                    );
                }
                if ($request->vendedor_id != null) {
                    $vendedor = Funcionario::where('empresa_id', $request->empresa_id)->first();
                    $vendedor->percentual_comissao;
                    $percentual_comissao = $vendedor->percentual_comissao;
                    $valorRetorno = $this->calcularComissaoVenda($vendaCaixa, $percentual_comissao);
                    ComissaoVenda::create([
                        'funcionario_id' => $request->vendedor_id,
                        'venda_id' => $vendaCaixa->id,
                        'tabela' => 'venda_caixas',
                        'valor' => $valorRetorno,
                        'status' => 0,
                        'empresa_id' => $request->empresa_id
                    ]);
                }
                if ($request->tipo_pagamento == '06') {
                    ContaReceber::create([
                        'venda_caixa_id' => $vendaCaixa->id,
                        'cliente_id' => $request->cliente_id,
                        'data_vencimento' => $request->data_vencimento,
                        'data_recebimento' => $request->data_vencimento,
                        'valor_integral' => __convert_value_bd($request->valor_total),
                        'valor_recebido' => 0,
                        'status' => 0,
                        'referencia' => "Parcela $i+1 da Compra código $vendaCaixa->id",
                        'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                        'empresa_id' => $request->empresa_id,
                        'juros' => 0,
                        'multa' => 0,
                        'observacao' => $request->obs ?? '',
                        'tipo_pagamento' => $request->tipo_pagamento
                    ]);
                }
                if ($request->tipo_pagamento_row) {
                    for ($i = 0; $i < sizeof($request->tipo_pagamento_row); $i++) {
                        if ($request->tipo_pagamento_row[$i] == '06') {
                            ContaReceber::create([
                                'venda_caixa_id' => $vendaCaixa->id,
                                'cliente_id' => $request->cliente_id,
                                'data_vencimento' => $request->data_vencimento_row[$i],
                                'data_recebimento' => $request->data_vencimento_row[$i],
                                'valor_integral' => __convert_value_bd($request->valor_integral_row[$i]),
                                'valor_recebido' => 0,
                                'status' => 0,
                                'referencia' => "Parcela $i+1 da Compra código $vendaCaixa->id",
                                'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->where('tipo', 'receber')->first()->id,
                                'empresa_id' => $request->empresa_id,
                                'juros' => 0,
                                'multa' => 0,
                                'observacao' => $request->obs_row[$i] ?? '',
                                'tipo_pagamento' => $request->tipo_pagamento_row[$i]
                            ]);
                        }
                        FaturaFrenteCaixa::create([
                            'valor' => __convert_value_bd($request->valor_integral_row[$i]),
                            'forma_pagamento' => $request->tipo_pagamento_row[$i],
                            'venda_caixa_id' => $vendaCaixa->id
                        ]);
                    }
                }
                if ($request->codigo_comanda != 0) {
                    $pedido = Pedido::where('comanda', $request->codigo_comanda)->first();
                    if($pedido != null){
                        $pedido->desativado = true;
                        $pedido->save();
                    }
                }
                return $vendaCaixa;
            });
return response()->json($vendaCaixa, 200);
} catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
    __saveLogError($e, request()->empresa_id);
    return response()->json($e->getMessage() . ", line: " . $e->getLine() . ", file: " . $e->getFile(), 401);
}
}

private function somaItens($request)
{
    $valor_total = 0;
    for ($i = 0; $i < sizeof($request->produto_id); $i++) {
        $valor_total += __convert_value_bd($request->subtotal_item[$i]);
    }
    return $valor_total;
}

private function calcularComissaoVenda($vendaCaixa, $percentual_comissao)
{
    $valorRetorno = 0;
    foreach ($vendaCaixa->itens as $i) {
        if ($i->produto->perc_comissao > 0) {
            $valorRetorno += (($i->valor * $i->quantidade) * $i->produto->perc_comissao) / 100;
        } else {
            $valorRetorno += (($i->valor * $i->quantidade) * $percentual_comissao) / 100;
        }
    }
    return $valorRetorno;
}
}
