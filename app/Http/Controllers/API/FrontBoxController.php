<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Produto;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class FrontBoxController extends Controller
{
    public function linhaProdutoVenda(Request $request){
        try{
            $qtd = $request->qtd;
            $value_unit = __convert_value_bd($request->value_unit);
            $sub_total = __convert_value_bd($request->sub_total);
            $product_id = $request->product_id;
            $key = $request->key;

            $product = Produto::findOrFail($product_id);
            return view('frontBox.partials.row_frontBox', compact('product', 'qtd', 'value_unit' ,'sub_total', 'key'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }
    public function linhaParcelaVenda(Request $request){
        try{
            $tipo_pagamento_row = $request->tipo_pagamento_row;
            $data_vencimento_row = $request->data_vencimento_row;
            $valor_integral_row = $request->valor_integral_row;
            $quantidade = $request->quantidade;
            $obs_row = $request->obs_row;

            $tipo = VendaCaixa::getTipoPagamento($tipo_pagamento_row);
            return view('frontBox.partials.row_pagMulti', compact('valor_integral_row',
            'data_vencimento_row', 'quantidade', 'tipo', 'obs_row', 'tipo_pagamento_row'));
        }catch(\Exception $e){
            return response()->json($e->getMessage(), 401);
        }
    }

}
