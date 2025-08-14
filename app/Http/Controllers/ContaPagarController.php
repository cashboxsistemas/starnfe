<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\CategoriaConta;
use App\Models\ContaPagar;
use App\Models\ConfigNota;
use App\Models\Fornecedor;
use Illuminate\Support\Facades\DB;

class ContaPagarController extends Controller
{
    public function index(Request $request)
    {
        $fornecedores = Fornecedor::where('empresa_id', request()->empresa_id)->get();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $fornecedor_id = $request->get('fornecedor_id');
        $type_search = $request->get('type_search');
        $status = $request->get('status');
        $filial_id = $request->get('filial_id');
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = ContaPagar::where('empresa_id', $request->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date, $type_search) {
                return $query->whereDate($type_search, '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
                return $query->whereDate($type_search, '<=', $end_date);
            })
            ->when(!empty($fornecedor_id), function ($query) use ($fornecedor_id) {
                return $query->where('fornecedor_id', $fornecedor_id);
            })
            ->when($status != "", function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->when($filial_id != 'todos', function ($query) use ($filial_id) {
                $filial_id = $filial_id == -1 ? null : $filial_id;
                return $query->where('filial_id', $filial_id);
            })
            ->orderBy('data_vencimento', 'asc')
            ->paginate(env("PAGINACAO"));
        return view('conta_pagar.index', compact('data', 'fornecedores', 'filial_id'));
    }

    public function create(Request $request)
    {
        $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)->get();
        $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
            ->where('tipo', 'pagar')
            ->orderBy('nome')
            ->get();
        return view('conta_pagar.create', compact('categorias', 'fornecedores'));
    }

    public function edit(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $fornecedores = Fornecedor::where('empresa_id', $request->empresa_id)->get();
        $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
            ->where('tipo', 'pagar')
            ->orderBy('nome')
            ->get();
        return view('conta_pagar.edit', compact('categorias', 'item', 'fornecedores'));
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $result = DB::transaction(function () use ($request) {
                $request->merge([
                    'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
                ]);
                $data = [
                    'compra_id' => null,
                    'data_vencimento' => $request->data_vencimento,
                    'data_pagamento' => $request->data_vencimento,
                    'valor_integral' => __convert_value_bd($request->valor_integral),
                    'valor_pago' => $request->status ? __convert_value_bd($request->valor_integral) : 0,
                    'referencia' => $request->referencia,
                    'categoria_id' => $request->categoria_id,
                    'status' => $request->status,
                    'empresa_id' => $request->empresa_id,
                    'fornecedor_id' => $request->fornecedor_id,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'filial_id' => $request->filial_id
                ];
                $item = ContaPagar::create($data);
                if ($request->dt_recorrencia) {
                    for ($i = 0; $i < sizeof($request->dt_recorrencia); $i++) {
                        $data = $request->dt_recorrencia[$i];
                        $valor = __convert_value_bd($request->valor_recorrencia[$i]);
                        $data = [
                            'compra_id' => null,
                            'data_vencimento' => $data,
                            'data_pagamento' => $data,
                            'valor_integral' => $valor,
                            'valor_pago' => $request->status ? $valor : 0,
                            'referencia' => $request->referencia,
                            'categoria_id' => $request->categoria_id,
                            'status' => $request->status,
                            'empresa_id' => $request->empresa_id,
                            'fornecedor_id' => $request->fornecedor_id,
                            'tipo_pagamento' => $request->tipo_pagamento
                        ];
                        ContaPagar::create($data);
                    }
                }
                return $item;
            });
            session()->flash("flash_sucesso", "Conta a pagar cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-pagar.index');
    }

    public function update(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        try {
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
            ]);
            $item->filial_id = $request->filial_id;
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Conta a pagar atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-pagar.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'fornecedor_id' => 'required',
            'referencia' => 'required',
            'valor_integral' => 'required',
            'data_vencimento' => 'required',
        ];
        $messages = [
            'referencia.required' => 'O campo referencia é obrigatório.',
            'fornecedor_id.required' => 'O campo fornecedor é obrigatório.',
            'valor_integral.required' => 'O campo valor é obrigatório.',
            'data_vencimento.required' => 'O campo vencimento é obrigatório.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy($id)
    {
        $item = ContaPagar::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Conta removida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-pagar.index');
    }

    public function pay($id)
    {
        $item = ContaPagar::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('conta_pagar.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $item = ContaPagar::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->valor_pago = __convert_value_bd($request->valor_pago);
            $item->status = true;
            $item->data_pagamento = $request->data_pagamento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->save();
            session()->flash("flash_sucesso", "Conta a paga!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-pagar.index');
    }
}
