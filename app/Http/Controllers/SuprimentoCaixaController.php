<?php

namespace App\Http\Controllers;

use App\Models\SuprimentoCaixa;
use Illuminate\Http\Request;

class SuprimentoCaixaController extends Controller
{
    protected $empresa_id = null;
    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->empresa_id = $request->empresa_id;
            $value = session('user_logged');
            if (!$value) {
                return redirect("/login");
            }
            return $next($request);
        });
    }

    public function store(Request $request)
    {
        try {
            $result = SuprimentoCaixa::create([
                'usuario_id' => get_id_user(),
                'valor' => __convert_value_bd($request->valor),
                'observacao' => $request->observacao ?? '',
                'empresa_id' => $request->empresa_id
            ]);
            session()->flash("flash_sucesso", "Suprimento realizado com sucesso testestes!");
        } catch (\Exception $e) {
            echo $e->getMessage();
            session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }
}
