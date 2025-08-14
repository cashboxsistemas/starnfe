<?php

namespace App\Http\Controllers;

use App\Models\Acessor;
use App\Models\Cliente;
use App\Models\ConfigNota;
use App\Models\Funcionario;
use App\Models\FuncionarioOs;
use App\Models\GrupoCliente;
use App\Models\OrdemServico;
use App\Models\Pais;
use App\Models\ProdutoOs;
use App\Models\RelatorioOs;
use App\Models\Servico;
use App\Models\ServicoOs;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Dompdf\Dompdf;

use function PHPUnit\Framework\returnSelf;

class OrderController extends Controller
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

    public function index(Request $request)
    {
        $start_date = $request->get('start_date');
        $end_date = $request->get('end_date');
        $cliente_id = $request->get('cliente_id');
        $estado = $request->get('estado');
        $data = OrdemServico::where('empresa_id', $request->empresa_id)
        ->when(!empty($start_date), function ($query) use ($start_date) {
            return $query->whereDate('created_at', '>=', $start_date);
        })
        ->when(!empty($end_date), function ($query) use ($end_date) {
            return $query->whereDate('created_at', '<=', $end_date);
        })
        ->when(!empty($cliente_id), function ($query) use ($cliente_id) {
            return $query->where('cliente_id', $cliente_id);
        })
        ->when($estado != "", function ($query) use ($estado) {
            return $query->where('estado', $estado);
        })
        ->orderBy('created_at', 'asc')
        ->paginate(env("PAGINACAO"));
        return view('ordem_servico.index', compact('data'));
    }

    public function create(Request $request)
    {
        $clientes = Cliente::where('empresa_id', request()->empresa_id)->get();
        $paises = Pais::all();
        $grupos = GrupoCliente::where('empresa_id', request()->empresa_id)->get();
        $acessores = Acessor::where('empresa_id', request()->empresa_id)->get();
        $funcionarios = Funcionario::where('empresa_id', request()->empresa_id)->get();
        return view(
            'ordem_servico.create',
            compact(
                'clientes',
                'paises',
                'grupos',
                'acessores',
                'funcionarios'
            )
        );
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        try {
            $ordem = OrdemServico::create([
                'descricao' => $request->input('descricao'),
                'usuario_id' => get_id_user(),
                'cliente_id' => $request->cliente_id,
                'empresa_id' => $request->empresa_id,
                'estado' => 'pendente'
            ]);
            session()->flash("flash_sucesso", "Ordem de Serviço criada com sucesso");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    private function _validate(Request $request)
    {
        $rules = [
            'cliente_id' => 'required',
            'descricao' => 'required'
        ];
        $messages = [
            'cliente_id' => 'Campo Obrigatório',
            'descricao.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $messages);
    }

    public function completa($id)
    {
        $ordem = OrdemServico::findOrFail($id);
        if (!__valida_objeto($ordem)) {
            abort(403);
        }
        $funcionarios = Funcionario::where('empresa_id', $this->empresa_id)->get();
        $servicos = Servico::where('empresa_id', $this->empresa_id)->get();
        $relatorio = RelatorioOs::all();
        return view(
            'ordem_servico.ordem_completa',
            compact('funcionarios', 'ordem', 'servicos', 'relatorio')
        );
    }

    public function storeFuncionario(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        $this->_validateFuncionario($request);
        try {
            FuncionarioOs::create([
                'usuario_id' => get_id_user(),
                'funcionario_id' => $request->funcionario_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'funcao' => $request->funcao
            ]);
            session()->flash("flash_sucesso", "Funcionario Adicionado a OS");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    private function _validateFuncionario(Request $request)
    {
        $rules = [
            'funcao' => 'required',
        ];
        $messages = [
            'funcao' => 'Campo Obrigatório',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function deleteFuncionario(Request $request, $id)
    {
        $item = FuncionarioOs::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $item->delete();
            session()->flash("flash_sucesso", "Funcionário removido");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function storeServico(Request $request)
    {

        try {

            ServicoOs::create([
                'servico_id' => $request->servico_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor_unitario' => __convert_value_bd($request->valor),
                'sub_total' => (float)__convert_value_bd($request->valor) * (float)__convert_value_bd($request->quantidade),
            ]);

            $this->calcTotal($request->ordem_servico_id);

            session()->flash("flash_sucesso", "Serviço adicionado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function storeProduto(Request $request)
    {
        try {
            ProdutoOs::create([
                'produto_id' => $request->produto_id,
                'ordem_servico_id' => $request->ordem_servico_id,
                'quantidade' => __convert_value_bd($request->quantidade),
                'valor_unitario' => __convert_value_bd($request->valor_unitario),
                'sub_total' => __convert_value_bd($request->valor_unitario) * __convert_value_bd($request->quantidade),
            ]);
            $this->calcTotal($request->ordem_servico_id);

            session()->flash("flash_sucesso", "Produto adicionado!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->back();
    }

    public function deleteServico(Request $request, $id)
    {
        $item = ServicoOs::findOrfail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
      
        try {
            $item->delete();
            $this->calcTotal($item->ordem_servico_id);

            session()->flash("flash_sucesso", "Serviço removido");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    private function calcTotal($id){
        $item = OrdemServico::findOrFail($id);
        $total = 0;
        foreach($item->servicos as $s){
            $total += $s->sub_total;
        }

        foreach($item->produtos as $p){
            $total += $p->sub_total;
        }
        $item->valor = $total;
        $item->save();
    }

    public function deleteProduto($id){
        $item = ProdutoOs::findOrFail($id); 
        if (!__valida_objeto($item)) {
            abort(403);
        }

        try{
            $item->delete();
            $this->calcTotal($item->ordem_servico_id);

            session()->flash('flash_sucesso', 'Registro removido!');
        }catch(\Exception $e){
            session()->flash('flash_erro', 'Algo deu errado: ' . $e->getMessage());
        }

        return redirect()->back();
        
    }

    public function addRelatorio($id)
    {
        $ordem = OrdemServico::where('id', $id)->first();
        return view('ordem_servico.add_relatorio', compact('ordem'));
    }

    public function storeRelatorio(Request $request)
    {
        $this->_validateRelatorio($request);
        $id = $request->ordem_servico_id;
        $ordem = OrdemServico::findOrFail($id);
        try {
            RelatorioOs::create([
                'usuario_id' => get_id_user(),
                'texto' => $request->texto,
                'ordem_servico_id' => $ordem->id
            ]);
            session()->flash("flash_sucesso", "Relatório Adicionado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    private function _validateRelatorio(Request $request)
    {
        $rules = [
            'texto' => 'required|min:15',
        ];
        $messages = [
            'texto.required' => 'O campo texto é obrigatório.',
            'texto.min' => 'Minimo de 15 caracteres.',
        ];
        $this->validate($request, $rules, $messages);
    }

    public function alterarStatusServico(Request $request, $id)
    {
        $servicoOs = ServicoOs::where('id', $id)->first();
        try {
            $servicoOs->status = !$servicoOs->status;
            $servicoOs->save();
            session()->flash("flash_sucesso", "Status Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function deleteRelatorio(Request $request, $id)
    {
        $relatorioOs = RelatorioOs::where('id', $id)->first();
        try {
            $relatorioOs->delete();
            session()->flash("flash_sucesso", "Relatório Deletado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->back();
    }

    public function editRelatorio($id)
    {
        $ordem = RelatorioOs::findOrFail($id);
        if (!__valida_objeto($ordem)) {
            abort(403);
        }
        return view('ordem_servico.edit_relatorio', compact('ordem'));
    }

    public function upRelatorio(Request $request)
    {
        $id = $request->ordem_servico_id;
        $ordem = RelatorioOs::findOrFail($id);
        try {
            $ordem->texto = $request->texto;
            $ordem->save();
            session()->flash("flash_sucesso", "Reletório Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    public function alterarEstado($id)
    {
        $ordem = OrdemServico::where('id', $id)->first();
        return view('ordem_servico.alterar_estado', compact('ordem'));
    }

    public function alterarEstadoPost(Request $request)
    {
        $ordem = OrdemServico::where('id', $request->id)->first();
        $result = $ordem->save();
        try {
            $ordem->estado = $request->novo_estado;
            $ordem->save();
            session()->flash("flash_sucesso", "Reletório Alterado");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, $request->empresa_id);
        }
        return redirect()->route('ordemServico.completa', $ordem->id);
    }

    public function imprimir($id){
        $ordem = OrdemServico::findOrFail($id);
        if(valida_objeto($ordem)){
            $config = ConfigNota::
            where('empresa_id', $this->empresa_id)
            ->first();

            if($config == null){
                return redirect('/configNF');
            }

            $p = view('ordem_servico/print')
            ->with('ordem', $ordem)
            ->with('config', $config);

            $domPdf = new Dompdf(["enable_remote" => true]);
            $domPdf->loadHtml($p);

            $pdf = ob_get_clean();

            $domPdf->setPaper("A4");
            $domPdf->render();
            $domPdf->stream("OS $ordem->numero_sequencial.pdf", array("Attachment" => false));

            
        }else{
            return redirect('/403');
        }
    }


    public function destroy($id)
    {
        $item = OrdemServico::findOrFail($id);
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            $this->removeItens($item);
            $item->delete();
            session()->flash("flash_sucesso", "Deletado com sucesso!");
        } catch (\Exception $e) {
            session()->flash("flash_erro", "Algo deu errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('ordemServico.index');
    }

    private function removeItens($item)
    {
        foreach ($item->servicos as $s) {
            $s->delete();
        }
        foreach ($item->relatorios as $s) {
            $s->delete();
        }
        foreach ($item->funcionarios as $s) {
            $s->delete();
        }
    }
}
