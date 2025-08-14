<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Categoria;
use App\Models\CategoriaProdutoDelivery;
use Illuminate\Support\Facades\DB;

class CategoriaController extends Controller
{

    public function index(Request $request){
        $data = Categoria::
        where('empresa_id', request()->empresa_id)
        ->when(!empty($request->nome), function ($q) use ($request) {
            return  $q->where(function ($quer) use ($request) {
                return $quer->where('nome', 'LIKE', "%$request->nome%");
            });
        })
        ->paginate(env("PAGINACAO"));
        return view('categorias/index', compact('data'));
    }

    public function create(){
        return view('categorias/create');
    }

    public function store(Request $request){
        $this->_validate($request);
        try{
            DB::transaction(function () use ($request) {
                Categoria::create($request->all());
            });
            session()->flash("flash_sucesso", "Categoria cadastrada!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categorias.index');
    }

    public function edit($id){
        $item = Categoria::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('categorias/edit', compact('item'));
    }

    public function update(Request $request, $id){
        $this->_validate($request);
        $item = Categoria::findOrFail($id);
        try{
            DB::transaction(function () use ($request, $item) {
                $item->fill($request->all())->save();
            });
            session()->flash("flash_sucesso", "Categoria atualizada!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categorias.index');
    }

    private function _validate(Request $request){
        $rules = [
            'nome' => 'required|max:50'
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'nome.max' => '50 caracteres maximos permitidos.'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function destroy(Request $request, $id){
        $item = Categoria::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try{
            $item->delete();
            session()->flash("flash_sucesso", "Categoria removida!");
        }catch(\Exception $e){
            session()->flash("flash_erro", "Algo deu Errado: Essa categoria esta sendo usada em outro cadastro! " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('categorias.index');
    }
}
