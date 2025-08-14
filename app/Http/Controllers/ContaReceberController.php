<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use App\Models\ContaReceber;
use Illuminate\Http\Request;
use App\Models\CategoriaConta;
use App\Models\Cidade;
use App\Models\Cliente;
use App\Models\Funcionario;
use App\Models\GrupoCliente;
use App\Models\Pais;
use Illuminate\Support\Facades\DB;

class ContaReceberController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $type_search = $request->get('type_search');
        $status = $request->get('status');
        $filial_id = $request->get('filial_id');
        $local_padrao = __get_local_padrao();
        if (!$filial_id && $local_padrao) {
            $filial_id = $local_padrao;
        }
        $data = ContaReceber::where('empresa_id', $request->empresa_id)
            ->when(empty($request->end_date), function ($q) use ($request) {
                return $q->whereBetween('data_vencimento', [
                    date("Y-m-d"),
                    date('Y-m-d', strtotime('+1 month'))
                ]);
            })
            ->when(!empty($start_date), function ($query) use ($start_date, $type_search) {
                return $query->whereDate($type_search, '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
                return $query->whereDate($type_search, '<=', $end_date);
            })
            ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
                return $query->where('cliente_id', $cliente_id);
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
        return view('conta_receber.index', compact('data', 'clientes', 'filial_id'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::where('empresa_id', $request->empresa_id)->get();
        $cidades = Cidade::all();
        $grupos = GrupoCliente::where('empresa_id', $request->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
        $paises = Pais::all();
        $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
            ->where('tipo', 'receber')
            ->orderBy('nome')
            ->get();
        return view('conta_receber.create', compact(
            'categorias',
            'cidades',
            'paises',
            'grupos',
            'acessores',
            'funcionarios',
            'clientes'
        ));
    }

    public function edit(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', $request->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', $request->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', $request->empresa_id)->get();
        $categorias = CategoriaConta::where('empresa_id', $request->empresa_id)
            ->where('tipo', 'receber')
            ->orderBy('nome')
            ->get();
        return view('conta_receber.edit', compact(
            'categorias',
            'item',
            'paises',
            'grupos',
            'acessores',
            'funcionarios'
        ));
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
                    'venda_id' => null,
                    'data_vencimento' => $request->data_vencimento,
                    'data_recebimento' => $request->data_vencimento,
                    'valor_integral' => __convert_value_bd($request->valor_integral),
                    'valor_recebido' => $request->status ? __convert_value_bd($request->valor_integral) : 0,
                    'referencia' => $request->referencia,
                    'categoria_id' => $request->categoria_id,
                    'status' => $request->status,
                    'empresa_id' => $request->empresa_id,
                    'cliente_id' => $request->cliente_id,
                    'tipo_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? '',
                    'filial_id' => $request->filial_id
                ];
                $item = ContaReceber::create($data);
                if ($request->dt_recorrencia) {
                    for ($i = 0; $i < sizeof($request->dt_recorrencia); $i++) {
                        $data = $request->dt_recorrencia[$i];
                        $valor = __convert_value_bd($request->valor_recorrencia[$i]);
                        $data = [
                            'venda_id' => null,
                            'data_vencimento' => $data,
                            'data_pagamento' => $data,
                            'valor_integral' => $valor,
                            'valor_recebido' => $request->status ? $valor : 0,
                            'referencia' => $request->referencia,
                            'categoria_id' => $request->categoria_id,
                            'status' => $request->status,
                            'empresa_id' => $request->empresa_id,
                            'cliente_id' => $request->cliente_id,
                            'tipo_pagamento' => $request->tipo_pagamento
                        ];
                        ContaReceber::create($data);
                    }
                }
                return $item;
            });
            session()->flash("flash_sucesso", "Conta a receber cadastrada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-receber.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'cliente_id' => 'required',
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

    public function update(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        try {
            $request->merge([
                'valor_integral' => __convert_value_bd($request->valor_integral),
                'filial_id' => $request->filial_id == -1 ? null : $request->filial_id
            ]);
            $item->filial_id = $request->filial_id;
            $item->fill($request->all())->save();
            session()->flash("flash_sucesso", "Conta a receber atualizada!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-receber.index');
    }

    public function destroy($id)
    {
        $item = ContaReceber::findOrFail($id);
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
        return redirect()->route('conta-receber.index');
    }

    public function pay($id)
    {
        $item = ContaReceber::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('conta_receber.pay', compact('item'));
    }

    public function payPut(Request $request, $id)
    {
        $item = ContaReceber::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->valor_recebido = __convert_value_bd($request->valor_pago);
            $item->status = true;
            $item->data_recebimento = $request->data_recebimento;
            $item->tipo_pagamento = $request->tipo_pagamento;
            $item->save();
            session()->flash("flash_sucesso", "Conta a recebida!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('conta-receber.index');
    }
}
