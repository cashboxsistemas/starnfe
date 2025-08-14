<?php

namespace App\Http\Controllers;

use App\Helpers\ProdutoGrade;
use App\Models\Categoria;
use App\Models\CategoriaProdutoEcommerce;
use App\Models\DivisaoGrade;
use App\Models\ImagemProdutoEcommerce;
use App\Models\Marca;
use App\Models\NaturezaOperacao;
use App\Models\Produto;
use App\Models\ProdutoEcommerce;
use App\Models\Tributacao;
use App\Utils\Util;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use App\Utils\UploadUtil;

class ProdutoEcommerceController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function index(Request $request)
    {
        $dataValidate = [
            'tributacaos', 'natureza_operacaos'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $data = ProdutoEcommerce::where('empresa_id', request()->empresa_id)
            ->when(!empty($request->nome), function ($q) use ($request) {
                return  $q->where(function ($quer) use ($request) {
                    return $quer->where('descricao', 'LIKE', "%$request->descricao%");
                });
            })
            ->paginate(env("PAGINACAO"));
        return view('produtos_ecommerce.index', compact('data'));
    }

    public function create(Request $request)
    {
        $dataValidate = [
            'categoria_produto_ecommerces', 'tributacaos', 'natureza_operacaos'
        ];
        $util = new Util();
        $validateEntry = $util->validateEntry($dataValidate, $request->empresa_id);
        if ($validateEntry != null) {
            session()->flash("flash_erro", $validateEntry['message']);
            return redirect($validateEntry['route']);
        }
        $marcas = Marca::where('empresa_id', $request->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', $request->empresa_id)->get();
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', $request->empresa_id)
            ->get();
        $naturezaPadrao = NaturezaOperacao::where('empresa_id', $request->empresa_id)
            ->first();
        $tributacao = Tributacao::where('empresa_id', $request->empresa_id)
            ->first();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', $request->empresa_id)
            ->where('sub_divisao', true)
            ->get();
        return view(
            'produtos_ecommerce.create',
            compact(
                'categorias',
                'marcas',
                'categoriasEcommerce',
                'naturezaPadrao',
                'tributacao',
                'divisoes',
                'subDivisoes'
            )
        );
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'valor' => __convert_value_bd($request->valor),
                'categoria_id' => $request->categoriaEcommerce_id,
                'percentual_desconto_view' => $request->percentual_desconto_view ?? 0,
                'status' => $request->status ?? 0,
                'sub_categoria_id' => $request->sub_categoriaEcommerce_id ?? 0,
                'controlar_estoque' => $request->controlar_estoque ?? 0,
                'destaque' => $request->destaque ?? 0,
                'descricao' => $request->descricao ?? ''
            ]);
            $item = ProdutoEcommerce::create($request->all());
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/produtoEcommerce');
                ImagemProdutoEcommerce::create([
                    'produto_id' => $item->id,
                    'path' => $file_name
                ]);
            }
            session()->flash("flash_sucesso", "Produto cadastrado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado!" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoEcommerce.index');
    }

    public function edit($id)
    {
        $item = ProdutoEcommerce::findOrFail($id);
        $categoriasEcommerce = CategoriaProdutoEcommerce::where('empresa_id', request()->empresa_id)->get();
        $categorias = Categoria::where('empresa_id', request()->empresa_id)->get();
        $divisoes = DivisaoGrade::where('sub_divisao', false)->get();
        $subDivisoes = DivisaoGrade::where('empresa_id', request()->empresa_id)
            ->where('sub_divisao', true)
            ->get();
        return view('produtos_ecommerce.edit', compact('item', 'categoriasEcommerce', 'categorias', 'divisoes', 'subDivisoes'));
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = ProdutoEcommerce::findOrFail($id);
        try {
            $request->merge([
                'produto_id' => $request->produto_id,
                'valor' => __convert_value_bd($request->valor),
                'categoria_id' => $request->categoriaEcommerce_id,
                'percentual_desconto_view' => $request->percentual_desconto_view ?? 0,
                'status' => $request->status ?? 0,
                'sub_categoria_id' => $request->sub_categoriaEcommerce_id ?? 0,
                'controlar_estoque' => $request->controlar_estoque ?? 0,
                'destaque' => $request->destaque ?? 0,
                'descricao' => $request->descricao ?? ''
            ]);
            $item->fill($request->all())->save();
            $file_name = '';
            if ($request->hasFile('image')) {
                $file_name = $this->util->uploadImage($request, '/produtoEcommerce');
                ImagemProdutoEcommerce::create([
                    'produto_id' => $item->id,
                    'path' => $file_name
                ]);
            }
            session()->flash("flash_sucesso", "Produto alterado com sucesso!");
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_erro", "Algo deu Errado!" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoEcommerce.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            //'descricao' => 'required',
            'valor' => 'required',
            //'cep' => 'required',
            'categoriaEcommerce_id' => 'required',
            'produto_id' => 'required'
        ];
        $messages = [
            //'descricao.required' => 'Campo Obrigatório',
            'valor.required' => 'Campo Obrigatório',
            //'cep.required' => 'Campo Obrigatório',
            'categoriaEcomerce_id.required' => 'Campo Obrigatório',
            'produto_id.required' => 'Campo Obrigatório'
        ];

        $this->validate($request, $rules, $messages);
    }

    public function galeria($id)
    {
        $item = ProdutoEcommerce::findOrFail($id);
        return view('produtos_ecommerce.galery', compact('item'));
    }

    public function saveImagem(Request $request)
    {
        $file_name = '';
        if ($request->hasFile('image')) {
            $file_name = $this->util->uploadImage($request, '/produtoEcommerce');
        }
        $request->merge([
            'path' => $file_name
        ]);
        $result = ImagemProdutoEcommerce::create($request->all());
        if ($result) {
            session()->flash("flash_sucesso", "Imagem cadastrada com sucesso!");
        } else {
            session()->flash('flash_erro', 'Erro ao cadastrar produto!');
        }
        return redirect()->route('produtoEcommerce.galeria', [$request->produto_id]);
    }

    public function deleteImagem($id)
    {
        $item = ImagemProdutoEcommerce::findOrFail($id);
        try {
            $this->util->unlinkImage($item, 'produtoDelivery', 'path');
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function destroy(Request $request, $id)
    {
        $item = ProdutoEcommerce::findOrFail($id);
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('produtoEcommerce.index');
    }
}
