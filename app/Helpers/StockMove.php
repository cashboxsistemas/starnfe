<?php

namespace App\Helpers;

use App\Models\Estoque;
use App\Models\Produto;
use App\Models\Empresa;

class StockMove
{

	private function existStock($productId, $filial_id)
	{
		$p = Estoque::where('produto_id', $productId)
			->when($filial_id > 0, function ($query) use ($filial_id) {
				return $query->where('filial_id', $filial_id);
			})
			->first();
		return $p != null ? $p : null;
	}

	public function getStockProduct($productId, $filial_id)
	{
		$stock = $this->existStock($productId, $filial_id);
		return $stock->quantity ?? 0;
	}

	public function pluStock($productId, $quantity, $value = -1, $filial_id = null)
	{
		$produto = Produto::find($productId);
		$quantity = (float)$quantity;
		$stock = $this->existStock($productId, $filial_id);
		if ($stock) { // update
			$stock->quantidade += $quantity;
			$stock->valor_compra = $value > -1 ? $value : $stock->valor_compra;
		} else {
			$stock = new Estoque();
			$stock->valor_compra = $value > -1 ? $value : $produto->valor_compra;
			$stock->quantidade = $quantity;
			$stock->produto_id = $productId;
			$stock->filial_id = $filial_id != -1 ? $filial_id : null;
			$stock->empresa_id = Empresa::getId();
		}
		return $stock->save();
	}

	public function downStock($productId, $quantity, $filial_id = null)
	{
		$produto = Produto::find($productId);
		$quantity = (float)$quantity;
		$stock = $this->existStock($productId, $filial_id);
		if ($stock) { // update
			$stock->quantidade -= $quantity;
			if ($stock->quantidade < 0.010) $stock->quantidade = 0;
			return $stock->save();
		} else {
			return 0;
		}
	}
}
