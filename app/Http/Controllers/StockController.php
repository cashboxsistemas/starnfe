<?php

namespace App\Http\Controllers;

use App\Helpers\StockMove;
use App\Models\AlteracaoEstoque;
use App\Models\Apontamento;
use App\Models\ConfigNota;
use App\Models\Estoque;
use App\Models\Filial;
use App\Models\Produto;
use Illuminate\Http\Request;

class StockController extends Controller
{
    public function index(Request $request)
    {
        $estoqueTotal = Estoque::select('estoques.*')
        ->orderBy('updated_at', 'desc')
        ->where('estoques.empresa_id', $request->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.id')
        ->get();

        $data = Estoque::orderBy('estoques.updated_at', 'desc')
        ->where('produtos.empresa_id', $request->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.produto_id')
        ->select('estoques.*')
        ->groupBy('produtos.id')
        ->paginate(env("PAGINACAO"));

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $totalProdutosEmEstoque = Estoque::select('estoques.*')
        ->where('produtos.empresa_id', $request->empresa_id)
        ->join('produtos', 'produtos.id', '=', 'estoques.id')
        ->count();

        $somaEstoque = $this->somaEstoque($estoqueTotal);
        return view('estoque.index', compact('data', 'estoqueTotal', 'totalProdutosEmEstoque', 'somaEstoque'));
    }


    private function somaEstoque($estoque)
    {
        $somaVenda = 0;
        $somaCompra = 0;
        foreach ($estoque as $e) {
            if ($e->produto) {
                $somaVenda += $e->produto->valor_venda * $e->quantidade;
                $somaCompra += $e->valorCompra() * $e->quantidade;
            }
        }
        return [
            'compra' => $somaCompra,
            'venda' => $somaVenda
        ];
    }

    public function create()
    {
        return view('estoque.createApontamento');
    }

    public function store(Request $request)
    {
        try {
            $request->merge([
                'usuario_id' => get_id_user(),
                'observacao' => $request->observacao ?? '',
            ]);

            Apontamento::create($request->all());
            AlteracaoEstoque::create([
                'empresa_id' => $request->empresa_id,
                'usuario_id' => get_id_user(),
                'produto_id' => $request->produto_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'observacao' => $request->observacao ?? '',
                'tipo' => $request->tipo
            ]);
            if ($request->tipo == 1) {
                $stockMove = new StockMove();
                $stockMove->pluStock(
                    $request->produto_id,
                    __convert_value_bd($request->quantidade)
                );
            } else {
                $stockMove = new StockMove();
                $stockMove->downStock(
                    $request->produto_id,
                    __convert_value_bd($request->quantidade)
                );
            }
            session()->flash("flash_sucesso", "Apontamento com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('estoque.index');
    }

    public function storeApontamento(Request $request)
    {

        $this->_validateApontamento($request);
        $produto = $request->produto_id;
        $prod = Produto::where('id', $produto)
        ->first();
        $result = Apontamento::create([
            'quantidade' => __convert_value_bd($request->quantidade),
            'usuario_id' => get_id_user(),
            'produto_id' => $request->produto_id,
            'empresa_id' => $request->empresa_id
        ]);
        $stockMove = new StockMove();

        $erroEstoque = $this->validaEstoqueDisponivel($prod, str_replace(",", ".", $request->quantidade));
        if ($erroEstoque == "") {

            $stockMove->pluStock(
                $request->produto_id,
                __convert_value_bd($request->quantidade),
                str_replace(",", ".", $prod->valor_venda)
            );

            $this->downEstoquePorReceita($produto, str_replace(",", ".", $request->quantidade));

            if ($result) {
                session()->flash("flash_sucesso", "Apontamento cadastrado com sucesso!");
            } else {
                session()->flash('flash_erro', 'Erro ao cadastrar apontamento!');
            }
        } else {
            session()->flash('flash_erro', $erroEstoque);
        }
        return redirect()->route('estoque.apontamentoProducao');
    }

    private function validaEstoqueDisponivel($produto, $quantidade)
    {
        $msg = "";
        if ($produto->receita) {
            foreach ($produto->receita->itens as $i) {
                $qtd = $i->quantidade * $quantidade;
                if ($i->produto->estoqueAtual() < $qtd) {
                    $msg = "Estoque insuficiente do produto " . $i->produto->nome;
                }
            }
        }
        return $msg;
    }


    private function downEstoquePorReceita($idProduto, $quantidade)
    {
        $produto = Produto::where('id', $idProduto)
        ->first();
        if (valida_objeto($produto)) {
            $stockMove = new StockMove();
            if ($produto->receita) {
                foreach ($produto->receita->itens as $i) {
                    $stockMove->downStock($i->produto->id, $i->quantidade * $quantidade);
                }
            }
        } else {
            return redirect('/403');
        }
    }


    public function listaApontamento(Request $request)
    {
        $data = AlteracaoEstoque::where('empresa_id', $request->empresa_id)->get();
        return view('estoque.listaApontamento', compact('data'));
    }

    public function apontamentoProducao(Request $request)
    {
        $data = Apontamento::where('empresa_id', $request->empresa_id)->get();
        return view('estoque.apontamento_producao', compact('data'));
    }

    public function todosApontamentos(Request $request)
    {
        $data = Apontamento::where('empresa_id', $request->empresa_id)->get();
        return view('estoque.apontamento_todos', compact('data'));
    }

    private function _validateApontamento(Request $request)
    {
        $rules = [
            'produto_id' => 'required',
            'quantidade' => 'required',
        ];
        $messages = [
            'produto_id.required' => 'O campo produto é obrigatório.',
            'produto_id.min' => 'Clique sobre o produto desejado.',
            'quantidade.required' => 'O campo quantidade é obrigatório.',
            'quantidade.min' => 'Informe o valor do campo em casas decimais, ex: 1,000.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function setEstoqueLocais($produto_id)
    {
        $item = Produto::findOrFail($produto_id);
        $grade = Produto::produtosDaGrade($item->referencia_grade);
        $temp = json_decode($item->locais);
        $locais = [];
        foreach ($temp as $l) {
            if ($l == -1) {
                $locais[$l] = 'Matriz';
            } else {
                $filial = Filial::findOrFail($l);
                if ($filial != null) {
                    $locais[$l] = $filial->descricao;
                }
            }
        }
        return view('estoque.set_estoque_locais', compact('item', 'locais', 'grade'));
    }

    public function setEstoqueStore(Request $request)
    {
        $stockMove = new StockMove();
        try {
            $produto = Produto::findOrFail($request->produto_id);
            for ($i = 0; $i < sizeof($request->quantidade); $i++) {
                if (isset($request->produto_grade_id)) {
                    $produto = Produto::findOrFail($request->produto_grade_id[$i]);
                }
                $stockMove->pluStock(
                    $produto->id,
                    __convert_value_bd($request->quantidade[$i]),
                    -1,
                    $request->filial_id[$i]
                );
            }
            session()->flash('flash_sucesso', 'Ação de estoque realizada!');
            if ($produto->composto == true) {
                return redirect()->route('produtosComposto.create', [$produto->id]);
            }
            return redirect()->route('estoque.index');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu  errado: ' . $e->getMessage());
            return redirect()->back();
        }
    }
}
