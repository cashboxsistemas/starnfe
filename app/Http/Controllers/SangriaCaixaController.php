<?php

namespace App\Http\Controllers;

use App\Models\AberturaCaixa;
use App\Models\ConfigNota;
use App\Models\ListaPreco;
use App\Models\SangriaCaixa;
use App\Models\SuprimentoCaixa;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;

class SangriaCaixaController extends Controller
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
			if ($request->valor <= $this->somaTotalEmCaixa()) {
				$result = SangriaCaixa::create([
					'usuario_id' => get_id_user(),
					'valor' => __convert_value_bd($request->valor),
					'observacao' => $request->observacao ?? '',
					'empresa_id' => $request->empresa_id
				]);
				session()->flash("flash_sucesso", "Sangria realizada com sucesso testestes!");
			} else {
				session()->flash("flash_erro", "Valor de sangria ultrapassa valor em caixa!");
			}
		} catch (\Exception $e) {
			echo $e->getMessage();
			session()->flash("flash_erro", "Algo deu errado: " . $e->getMessage());
			__saveLogError($e, request()->empresa_id);

		}
		return redirect()->back();
	}

	private function somaTotalEmCaixa()
	{
		$abertura = AberturaCaixa::where('empresa_id', $this->empresa_id)->where('status', 0)
		->orderBy('id', 'desc')
		->first();
		if ($abertura == null) return 0;
		$soma = 0;
		$soma += $abertura->valor;
		$vendasPdv = VendaCaixa::whereBetween('id', [
			$abertura->primeira_venda_nfce,
			($abertura->primeira_venda_nfce > 0 ? $abertura->primeira_venda_nfce : 1) * 10000
		])
		->selectRaw('sum(valor_total) as valor')
		->where('empresa_id', $this->empresa_id)
		->first();
		if ($vendasPdv != null)
			$soma += $vendasPdv->valor;
		$vendas = Venda::whereBetween('id', [
			$abertura ? $abertura->primeira_venda_nfe : 0,
			($abertura->primeira_venda_nfe > 0 ? $abertura->primeira_venda_nfce : 1) * 10000
		])
		->selectRaw('sum(valor_total) as valor')
		->where('empresa_id', $this->empresa_id)
		->first();
		if ($vendas != null)
			$soma += $vendas->valor;
		$amanha = date('Y-m-d', strtotime('+1 days')) . " 00:00:00";
		$suprimentosSoma = SuprimentoCaixa::selectRaw('sum(valor) as valor')->whereBetween(
			'created_at',
			[$abertura->created_at, $amanha]
		)
		->where('empresa_id', $this->empresa_id)
		->first();
		if ($suprimentosSoma != null)
			$soma += $suprimentosSoma->valor;
		$sangriasSoma = SangriaCaixa::selectRaw('sum(valor) as valor')->whereBetween(
			'created_at',
			[$abertura->created_at, $amanha]
		)
		->where('empresa_id', $this->empresa_id)
		->first();
		if ($sangriasSoma != null)
			$soma -= $sangriasSoma->valor;
		return $soma;
	}
}
