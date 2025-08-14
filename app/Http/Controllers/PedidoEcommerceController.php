<?php

namespace App\Http\Controllers;

use App\Models\Cliente;
use App\Models\PedidoEcommerce;
use Illuminate\Http\Request;

class PedidoEcommerceController extends Controller
{
    public function index(Request $request)
    {
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $type_search = $request->get('type_search');
        $status = $request->get('status');
        $data = PedidoEcommerce::where('empresa_id', $request->empresa_id)
            ->when(empty($request->end_date), function ($q) use ($request) {
                return $q->whereBetween('created_at', [
                    date("Y-m-d"),
                    date('Y-m-d', strtotime('+1 month'))
                ]);
            })
            ->when(!empty($start_date), function ($query) use ($start_date, $type_search) {
                return $query->whereDate($type_search, '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date, $type_search) {
                return $query->whereDate($type_search, '<=', $end_date);
            })
            ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
                return $query->where('cliente_id', $cliente_id);
            })
            ->when($status != "", function ($query) use ($status) {
                return $query->where('status', $status);
            })
            ->orderBy('data_vencimento', 'asc')
            ->paginate(env("PAGINACAO"));
        return view('pedidos_ecommerce.index', compact('clientes', 'data'));
    }
}
