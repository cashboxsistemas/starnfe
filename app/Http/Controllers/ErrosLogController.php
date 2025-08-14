<?php

namespace App\Http\Controllers;

use App\Models\Empresa;
use App\Models\ErroLog;
use Illuminate\Http\Request;

class ErrosLogController extends Controller
{
    public function index(Request $request)
    {
        $empresas = Empresa::orderBy('razao_social')->get();
        $empresa_id = $request->get('empresa_id');
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $data = ErroLog::where('empresa_id', $request->empresa_id)
            ->when(!empty($start_date), function ($query) use ($start_date) {
                return $query->whereDate('created_at', '>=', $start_date);
            })
            ->when(!empty($end_date), function ($query) use ($end_date) {
                return $query->whereDate('created_at', '<=', $end_date);
            })
            ->when(!empty($empresa_id), function ($query) use ($empresa_id) {
                return $query->where('empresa_id', $empresa_id);
            })
            ->orderBy('created_at', 'asc')
            ->paginate(env("PAGINACAO"));

        return view('erros_log.index', compact('data', 'empresas'));
    }

    public function destroy($id)
    {
        try {
            $item = ErroLog::findOrFail($id);
            $item->delete();
            session()->flash('flash_sucesso', 'Erro removido!');
        } catch (\Exception $e) {
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('errosLog.index');
    }
}
