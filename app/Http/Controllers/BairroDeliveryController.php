<?php

namespace App\Http\Controllers;

use App\Models\BairroDelivery;
use App\Models\CidadeDelivery;
use App\Models\DeliveryConfig;
use Illuminate\Http\Request;

class BairroDeliveryController extends Controller
{
    public function index(Request $request)
    {
        $config = DeliveryConfig::where('empresa_id', $request->empresa_id)
            ->first();

        if ($config == null) {
            session()->flash('flash_erro', 'Configure o delivery primeiro!');
            return redirect()->route('configDelivery.index');
        }
        $bairrosDoSuper = BairroDelivery::where('cidade_id', $config->cidade_id)
            ->get();

        $cidades = CidadeDelivery::all();
        $data = BairroDelivery::when(!empty($request->nome), function ($q) use ($request) {
            return $q->where('nome', 'LIKE', "%$request->nome%");
        })
            ->when(!empty($request->cidade), function ($q) use ($request) {
                return $q->where('cidade_id', $request->cidade);
            })
            ->paginate(env("PAGINACAO"));
        return view('bairros_delivery.index', compact('data', 'cidades', 'bairrosDoSuper'));
    }

    public function create()
    {
        $cidades = CidadeDelivery::all();
        return view('bairros_delivery.create', compact('cidades'));
    }

    public function store(request $request)
    {
        try {
            $request->merge([
                'cidade_id' => $request->cidade
            ]);
            BairroDelivery::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDelivery.index');
    }

    public function edit($id)
    {
        $cidades = CidadeDelivery::all();

        $item = BairroDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        return view('bairros_delivery.edit', compact('item', 'cidades'));
    }

    public function update(Request $request, $id)
    {
        $item = BairroDelivery::findOrFail($id);
        try {
            $request->merge([
                'cidade_id' => $request->cidade
            ]);
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Atualizado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDelivery.index');
    }

    public function destroy($id)
    {
        $item = BairroDelivery::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash('flash_sucesso', 'Deletado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('bairrosDelivery.index');

    }

    
}
