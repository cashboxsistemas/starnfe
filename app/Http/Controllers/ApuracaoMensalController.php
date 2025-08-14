<?php

namespace App\Http\Controllers;

use App\Models\ApuracaoMensal;
use App\Models\ApuracaoSalarioEvento;
use App\Models\EventoSalario;
use App\Models\Funcionario;
use App\Models\ContaPagar;
use App\Models\CategoriaConta;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ApuracaoMensalController extends Controller
{
    public function index(Request $request)
    {
        $nome = $request->nome;
        $dt_inicio = $request->get('start_date');
        $dt_fim = $request->get('end_date');
        $data = ApuracaoMensal::select('apuracao_mensals.*')
        ->join('funcionarios', 'apuracao_mensals.funcionario_id', '=', 'funcionarios.id')
        ->where('empresa_id', request()->empresa_id)
        ->when(!empty($nome), function ($query) use ($nome) {
            return $query->where('funcionarios.nome', 'like', "%$nome%");
        })
        ->when(!empty($dt_inicio), function ($query) use ($dt_inicio) {
            return $query->whereDate('apuracao_salarios.created_at', '>=', $dt_inicio);
        })
        ->when(!empty($dt_fim), function ($query) use ($dt_fim) {
            return $query->whereDate('apuracao_salarios.created_at', '<=', $dt_fim);
        })
        ->paginate(env("PAGINACAO"));
        return view('apuracao_mensal.index', compact('nome', 'dt_inicio', 'dt_fim', 'data'));
    }

    public function create()
    {
        $funcionarios = Funcionario::orderBy('nome')
        ->where('empresa_id', request()->empresa_id)
        ->get();
        $mesAtual = (int)date('m') - 1;
        return view('apuracao_mensal.create', compact('mesAtual', 'funcionarios'));
    }

    public function getEventos($id)
    {
        try {
            $item = Funcionario::findOrFail($id);
            if (sizeof($item->eventos) == 0) {
                return response()->json("", 200);
            }
            return view('apuracao_mensal.eventos', compact('item'));
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 401);
        }
    }

    public function store(Request $request)
    {
        // dd($request);
        try {
            DB::transaction(function () use ($request) {
                $ap = [
                    'funcionario_id' => $request->funcionario,
                    'mes' => $request->mes,
                    'ano' => $request->ano,
                    'valor_final' => __convert_value_bd($request->valor_total),
                    'forma_pagamento' => $request->tipo_pagamento,
                    'observacao' => $request->observacao ?? ''
                ];
                $result = ApuracaoMensal::create($ap);
                for ($i = 0; $i < sizeof($request->evento); $i++) {
                    $ev = EventoSalario::find($request->evento[$i]);
                    if ($ev) {
                        ApuracaoSalarioEvento::create([
                            'apuracao_id' => $result->id,
                            'evento_id' => $ev->id,
                            'valor' => __convert_value_bd($request->evento[$i]),
                            'metodo' => $request->metodo[$i],
                            'condicao' => $request->condicao[$i],
                            'nome' => $ev->nome
                        ]);
                    }
                }

                if($request->conta_pagar){

                    $conta = ContaPagar::create([
                        'empresa_id' => $request->empresa_id,
                        'data_vencimento' => $request->vencimento,
                        'valor_integral' => __convert_value_bd($request->valor_total),
                        'referencia' => 'Apuração salário ' . $result->funcionario->nome,
                        'status' => $request->conta_paga,
                        'data_pagamento' => $request->vencimento,
                        'valor_pago' => $request->conta_paga ? __convert_value_bd($request->valor_total) : 0,
                        'tipo_pagamento' => $request->tipo_pagamento,
                        'categoria_id' => CategoriaConta::where('empresa_id', $request->empresa_id)->first()->id
                    ]);

                    $result->conta_pagar_id = $conta->id;
                    $result->save();
                }
            });
            session()->flash('flash_sucesso', 'Salvo com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }
        return redirect()->route('apuracaoMensal.index');
    }


    public function destroy($id)
    {
        $item = ApuracaoMensal::findOrFail($id);
        try {
            $item->eventos()->delete();
            $item->delete();
            session()->flash("flash_sucesso", "Registro removido!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado: " . $e->getMessage());
        }
        return redirect()->back();
    }
}
