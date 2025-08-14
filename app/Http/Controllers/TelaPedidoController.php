<?php

namespace App\Http\Controllers;

use App\Models\TelaPedido;
use Illuminate\Http\Request;

class TelaPedidoController extends Controller
{
    public function index()
    {
        $item = TelaPedido::where('empresa_id', request()->empresa_id)->get();
        return view('tela_pedido.index', compact('item'));
    }

    public function create()
    {
        return view('tela_pedido.create');
    }

    public function store(Request $request)
    {
        try {
            TelaPedido::create($request->all())->save();
            session()->flash('flash_sucesso', 'Cadastrado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('telasPedido.index');
    }

    public function edit($id)
    {
        $item = TelaPedido::findOrFail($id);
        return view('tela_pedido.edit', compact('item'));
    }

    public function update(Request $request, $id)
    {
        $item = TelaPedido::findOrFail($id);
        try {
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('telasPedido.index');
    }

    public function destroy($id)
    {
        $item = TelaPedido::findOrFail($id);
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Apagado com sucesso');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('telasPedido.index');
    }
}
