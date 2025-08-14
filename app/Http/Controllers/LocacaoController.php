<?php

namespace App\Http\Controllers;

use App\Models\ConfigNota;
use App\Models\ItemLocacao;
use App\Models\ItemLocacaoDisponibilidade;
use App\Models\Locacao;
use App\Models\Produto;
use Dompdf\Dompdf;
use Dompdf\Options;
use Illuminate\Http\Request;

class LocacaoController extends Controller
{
    public function index(Request $request)
    {
        $data = Locacao::where('empresa_id', $request->empresa_id)->paginate();
        return view('locacao.index', compact('data'));
    }

    public function create()
    {
        return view('locacao.create');
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'observacao' => $request->observacao ?? ''
            ]);
            $item = Locacao::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
            return redirect()->route('locacao.itens', $item->id);
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function edit($id)
    {
        $item = Locacao::findOrFail($id);
        return view('locacao.edit', compact('item'));
    }

    public function itens($id)
    {
        $item = Locacao::findOrFail($id);
        $produtos = Produto::where('empresa_id', request()->empresa_id)
            ->where('valor_locacao', '>', 0)
            ->get();
        return view('locacao.itens', compact('produtos', 'locacao'));
    }

    public function validaEstoque($produto_id, $locacao_id)
    {
        try {
            $produto = Produto::find($produto_id);
            $locacao = Locacao::find($locacao_id);
            $estoqueTotal = $produto->estoqueAtual();
            $diferenca = strtotime($locacao->fim) - strtotime($locacao->inicio);
            $dias = floor($diferenca / (60 * 60 * 24));
            $semEstoqueData = "";
            $date = $locacao->inicio;
            $estoqueDisponivel = $produto->estoqueAtual();
            $arrDatas = [];
            for ($i = 0; $i <= $dias; $i++) {
                $countTemp = ItemLocacaoDisponibilidade::where('produto_id', $produto_id)
                    ->whereDate('data', $date)
                    ->count();
                if ($countTemp >= $estoqueDisponivel && $semEstoqueData == "") {
                    $semEstoqueData = $date;
                }
                $date = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            }
            $valor_locacao = $produto->valor_locacao;
            $arr = [
                'valor_locacao' => $valor_locacao,
                'semEstoqueData' => $semEstoqueData != "" ? \Carbon\Carbon::parse($semEstoqueData)->format('d/m/Y') : ""
            ];
            return response()->json($arr, 200);
        } catch (\Exception $e) {
            return response()->json("erro: " . $e->getMessage(), 401);
        }
    }

    public function storeItem(Request $request)
    {
        $this->_validateItem($request);
        $request->merge(['observacao' => $request->observacao ?? '']);
        try {
            $locacao = Locacao::find($request->locacao_id);
            $diferenca = strtotime($locacao->fim) - strtotime($locacao->inicio);
            $dias = floor($diferenca / (60 * 60 * 24));
            $l = ItemLocacao::create($request->all());
            $date = $locacao->inicio;
            for ($i = 0; $i <= $dias; $i++) {
                ItemLocacaoDisponibilidade::create([
                    'produto_id' => $request->produto_id,
                    'data' => $date,
                    'locacao_id' => $locacao->id
                ]);
                $date = date('Y-m-d', strtotime("+1 days", strtotime($date)));
            }
            $locacao->total = $this->somaItens($locacao);
            $locacao->save();
            session()->flash('flash_sucesso', 'Item adicionado');
            return redirect()->back();
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    private function somaItens($locacao)
    {
        $total = 0;
        foreach ($locacao->itens as $i) {
            $total += $i->valor;
        }
        return $total;
    }

    private function _validateItem(Request $request)
    {
        $rules = [
            'produto_id' => 'required|numeric|min:1',
            'valor' => 'required'
        ];
        $messages = [
            'produto_id.required' => 'O campo produto é obrigatório.',
            'produto_id.min' => 'O campo produto é obrigatório.',
            'valor.required' => 'O campo valor é obrigatório.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function deleteItem($id)
    {
        $item = ItemLocacao::find($id);
        $locacao = Locacao::find($item->locacao_id);
        if (valida_objeto($locacao)) {
            ItemLocacaoDisponibilidade::where('locacao_id', $locacao->id)
                ->where('produto_id', $item->produto_id)->delete();
            $item->delete();
            $locacao->total = $this->somaItens($locacao);
            $locacao->save();
            session()->flash('flash_sucesso', 'Item removido');
            return redirect()->back();
        } else {
            return redirect('/403');
        }
    }

    public function storeObs(Request $request)
    {
        // dd($request);
        try {
            $locacao = Locacao::findOrFail($request->locacao);
            $locacao->observacao = $request->observacao;
            $locacao->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
        }
        return redirect()->back();
    }

    public function alterarStatus($id)
    {
        try {
            $locacao = Locacao::find($id);
            if (valida_objeto($locacao)) {
                $locacao->status = true;
                $locacao->save();
                session()->flash('flash_sucesso', 'Status alterado');
                ItemLocacaoDisponibilidade::where('locacao_id', $locacao->id)->delete();
                return redirect()->back();
            } else {
                return redirect('/403');
            }
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function comprovante($id)
    {
        try {
            $locacao = Locacao::find($id);
            if (valida_objeto($locacao)) {
                $config = ConfigNota::where('empresa_id', request()->empresa_id)
                    ->first();
                $p = view('locacao.comprovante', compact('config', 'locacao'));
                // return $p;
                $options = new Options();
                $options->set('isRemoteEnabled', TRUE);
                $domPdf = new Dompdf($options);
                $domPdf->loadHtml($p);
                $domPdf->setPaper("A4");
                $domPdf->render();
                // $domPdf->stream("orcamento.pdf", ["Attachment" => false]);
                $domPdf->stream("relatorio_locacao_$locacao->id.pdf", ["Attachment" => false]);
            } else {
                return redirect('/403');
            }
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }

    public function delete($id)
    {
        $item = Locacao::findOrFail($id);
        try{
            $item->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso!');
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('locacao.index');
    }
}
