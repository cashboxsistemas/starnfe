<?php

namespace App\Http\Controllers;

use App\Models\ClienteEcommerce;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

class ClienteEcommerceController extends Controller
{
    public function index(Request $request)
    {
        $data = ClienteEcommerce::where('empresa_id', $request->empresa_id)
        ->paginate(env('PAGINACAO'));
        return view('cliente_ecommerce.index', compact('data'));
    }

    public function create()
    {
        return view('cliente_ecommerce.create');
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $request->merge([
                'senha' => md5($request->senha),
                'status' => 1,
                'token' => Str::random(20)
            ]);
            ClienteEcommerce::create($request->all());
            session()->flash('flash_sucesso', 'Cadastrado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('clienteEcommerce.index');
    }

    public function edit($id)
    {
        $item = ClienteEcommerce::findOrFail($id);
        if (__valida_objeto($item)) {
            return view('cliente_ecommerce.edit', compact('item'));
        }
    }

    public function update(Request $request, $id)
    {
        $this->_validate($request);
        $item = ClienteEcommerce::findOrFail($id);
        try {
            if ($request->senha) {
                $item->senha = md5($request->senha);
            }
            $item->fill($request->all())->save();
            session()->flash('flash_sucesso', 'Alterado com sucesso!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('clienteEcommerce.index');
    }

    private function _validate(Request $request, $update = false)
    {
        $rules = [
            'nome' => 'required',
            'sobre_nome' => 'required',
            'email' => 'required',
            'telefone' => 'required',
            'cpf' => 'required',
            'senha' => !$update ? 'required' : '',
            'senha' => 'max:40'
        ];
        $messages = [
            'nome.required' => 'O campo nome é obrigatório.',
            'sobre_nome.required' => 'O campo sobre nome é obrigatório.',
            'senha.required' => 'O campo senha é obrigatório',
            'email.required' => 'O campo email é obrigatório',
            'telefone.required' => 'O campo telefone é obrigatório',
            'cpf.required' => 'O campo documento é obrigatório',
            'senha.max' => 'Máximo 40 caracteres'
        ];
        $this->validate($request, $rules, $messages);
    }

    // public function destroy($id)
    // {
    //     $item = ClienteEcommerce::findOrFail($id);
    //     if (__valida_objeto($item)) {
    //         try {
    //             $item->delete();
    //             session()->flash('flash_sucesso', 'Deletado com sucesso!');
    //         } catch (\Exception $e) {
    //             session()->flash('flash_erro', 'Algo deu errado:' . $e->getMessage());
    //             __saveLogError($e, request()->empresa_id);
    //         }
    //         return redirect()->route('clienteEcommerce.index');
    //     }
    // }

}
