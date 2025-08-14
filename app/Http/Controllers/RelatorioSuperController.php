<?php

namespace App\Http\Controllers;

use App\Models\Contador;
use App\Models\Cte;
use App\Models\Empresa;
use App\Models\Mdfe;
use App\Models\Plano;
use App\Models\UsuarioAcesso;
use App\Models\Venda;
use App\Models\VendaCaixa;
use Illuminate\Http\Request;
use Dompdf\Dompdf;
use NFePHP\Common\Certificate;

class RelatorioSuperController extends Controller
{
    public function index()
    {

        $empresas = Empresa::all();
        $planos = Plano::all();
        $contador = Contador::all();
        return view('relatorio_super.index', compact('empresas', 'planos', 'contador'));
    }

    public function empresas(Request $request)
    {
        $empresa = $request->empresa;
        $status = $request->status;
        $plano = $request->plano;
        // if($empresa == "null"){
        // 	session()->flash('mensagem_erro', 'Selecione uma empresa');
        // 	return redirect()->back();
        // }
        $empresas = Empresa::select('empresas.*');
        if ($empresa) {
            $empresas->where('id', $empresa);
        }
        if ($plano) {
            $empresas->join('plano_empresas', 'plano_empresas.empresa_id', '=', 'empresas.id');
            $empresas->where('plano_empresas.empresa_id', $request->plano);
        }
        if ($status != 'todos') {
            $temp = [];
            foreach ($empresas as $e) {
                if ($e->status() == $request->status) {
                    array_push($temp, $e);
                }
                if ($request->status == 2) {
                    if (!$e->planoEmpresa) {
                        array_push($temp, $e);
                    }
                }
            }
            $empresas = $temp;
        }
        $empresas = $empresas->get();
        $p = view('relatorio_super.empresas', compact('empresa', 'plano', 'empresas', 'status'));
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de empresas.pdf", array("Attachment" => false));
    }

    public function extratoCliente(Request $request)
    {
        if ($request->empresa == "") {
            session()->flash('flash_erro', 'Selecione uma empresa');
            return redirect()->back();
        }
        $empresa = Empresa::findOrFail($request->empresa);
        $acessos = $this->totalizaAcessos($request, $empresa);
        $totalNfe = $this->totalizaNFe($request);
        $totalNfce = $this->totalizaNFCe($request);
        $totalCte = $this->totalizaCTe($request);
        $totalMdfe = $this->totalizaMDFe($request);
        $totalVendas = $this->totalizaVendas($request);
        $totalizaVendasCaixa = $this->totalizaVendasCaixa($request);
        $p = view('relatorio_super.extrato_cliente')
            ->with('empresa', $empresa)
            ->with('acessos', $acessos)
            ->with('totalNfe', $totalNfe)
            ->with('totalVendas', $totalVendas)
            ->with('totalizaVendasCaixa', $totalizaVendasCaixa)
            ->with('totalNfce', $totalNfce)
            ->with('totalCte', $totalCte)
            ->with('totalMdfe', $totalMdfe)
            ->with('data_inicial', $request->start_date)
            ->with('data_final', $request->end_date);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Relatório de extrato de cliente.pdf", array("Attachment" => false));
    }

    private function totalizaAcessos($request, $empresa)
    {
        $usuarios = $empresa->usuarios;
        $cont = 0;
        foreach ($usuarios as $u) {
            if ($request->start_date && $request->end_date) {
                $dataInicial = $request->start_date;
                $dataFinal = $request->end_date;
                $acessos = UsuarioAcesso::where('usuario_id', $u->id)
                    ->whereBetween('created_at', [
                        $dataInicial,
                        $dataFinal
                    ])->count();
                if ($acessos) $cont += $acessos;
            } else {
                $cont += sizeof($u->acessos);
            }
        }
        return $cont;
    }

    private function totalizaNFe($request)
    {
        $vendas = Venda::where('empresa_id', $request->empresa)
            ->where('estado_emissao', 'APROVADO')
            ->where('numero_nfe', '>', 0);
        if ($request->start_date && $request->end_date) {
            $dataInicial = $request->start_date;
            $dataFinal = $request->end_date;
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    private function totalizaNFCe($request)
    {
        $vendas = VendaCaixa::where('empresa_id', $request->empresa)
            ->where('estado_emissao', 'APROVADO')
            ->where('numero_nfce', '>', 0);
        if ($request->start_date && $request->end_date) {
            $dataInicial = $request->start_date;
            $dataFinal = $request->end_date;
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    private function totalizaCTe($request)
    {
        $vendas = Cte::where('empresa_id', $request->empresa)
            ->where('cte_numero', '>', 0);
        if ($request->start_date && $request->end_date) {
            $dataInicial = $this->parseDate($request->start_date);
            $dataFinal = $this->parseDate($request->end_date, true);
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    private function totalizaMDFe($request)
    {
        $vendas = Mdfe::where('empresa_id', $request->empresa)
            ->where('mdfe_numero', '>', 0);
        if ($request->start_date && $request->end_date) {
            $dataInicial = $this->parseDate($request->start_date);
            $dataFinal = $this->parseDate($request->end_date, true);
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    private function totalizaVendas($request)
    {
        $vendas = Venda::where('empresa_id', $request->empresa)
            ->where('estado_emissao', '!=', 'CANCELADO');
        if ($request->start_date && $request->end_date) {
            $dataInicial = $this->parseDate($request->start_date);
            $dataFinal = $this->parseDate($request->end_date, true);
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    private function totalizaVendasCaixa($request)
    {
        $vendas = VendaCaixa::where('empresa_id', $request->empresa)
            ->where('estado', '!=', 'CANCELADO');
        if ($request->start_date && $request->end_date) {
            $dataInicial = $this->parseDate($request->start_date);
            $dataFinal = $this->parseDate($request->end_date, true);
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        return $vendas->count();
    }

    public function historicoAcessos(Request $request)
    {
        $empresas = Empresa::orderBy('id', 'desc')->get();
        $data = [];
        foreach ($empresas as $e) {
            $request->empresa = $e->id;
            $acessos = $this->totalizaAcessos($request, $e);
            $totalNfe = $this->totalizaNFe($request);
            $totalNfce = $this->totalizaNFCe($request);
            $totalBruto = $this->totalizaVendasBruta($request);
            $item = [
                'empresa' => $e->nome,
                'acessos' => $acessos,
                'nfes' => $totalNfe,
                'nfces' => $totalNfce,
                'bruto' => $totalBruto,
                'data_cadastro' => \Carbon\Carbon::parse($e->created_at)->format('d/m/Y H:i'),
                'plano_nome' => $e->planoEmpresa ? $e->planoEmpresa->plano->nome : '--',
                'plano_valor' => $e->planoEmpresa ? $e->planoEmpresa->valor : 0
            ];
            if ($acessos > 0)
                array_push($data, $item);
        }
        usort($data, function ($a, $b) {
            return $a['acessos'] < $b['acessos'] ? 1 : 0;
        });
        $p = view('relatorio_super.extrato_acessos')
            ->with('data', $data)
            ->with('data_inicial', $request->data_inicial)
            ->with('data_final', $request->data_final);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de extrato de cliente.pdf");
    }

    private function totalizaVendasBruta($request)
    {
        $vendas = Venda::where('empresa_id', $request->empresa)
            ->select(\DB::raw('SUM(valor_total) as total'));
        if ($request->data_inicial && $request->data_final) {
            $dataInicial = $request->start_date;
            $dataFinal = $request->end_date;
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        $vendas = $vendas->first();
        $soma = $vendas->total ?? 0;
        $vendas = VendaCaixa::where('empresa_id', $request->empresa)
            ->select(\DB::raw('SUM(valor_total) as total'));
        if ($request->start_date && $request->end_date) {
            $dataInicial = $request->start_date;
            $dataFinal = $request->end_date;
            $vendas->whereBetween('created_at', [
                $dataInicial,
                $dataFinal
            ]);
        }
        $vendas = $vendas->first();
        $soma += $vendas->total ?? 0;
        return $soma;
    }

    public function certificados(Request $request)
    {
        $data_inicial = $request->start_date;
        $data_final = $request->end_date;
        $status = $request->status;
        $dataHoje = date('Y-m-d');
        $empresas = Empresa::all();
        $temp = [];
        $dtInicial = $data_inicial;
        $dtFinal = $data_final;
        foreach ($empresas as $e) {
            if ($e->certificado) {
                $infoCertificado = Certificate::readPfx($e->certificado->arquivo, $e->certificado->senha);
                $publicKey = $infoCertificado->publicKey;
                $e->vencimento = $publicKey->validTo->format('Y-m-d');
                $e->vencido = strtotime($dataHoje) > strtotime($e->vencimento);
                if ($data_inicial && $data_final) {
                    if ((strtotime($e->vencimento) > strtotime($dtInicial)) && (strtotime($e->vencimento) < strtotime($dtFinal))) {
                        array_push($temp, $e);
                    }
                } else if ($status != 'TODOS') {
                    if ($status == 1 && $e->vencido) {
                        array_push($temp, $e);
                    } elseif ($status == 2 && !$e->vencido) {
                        array_push($temp, $e);
                    }
                } else {
                    array_push($temp, $e);
                }
                usort($temp, function ($a, $b) {
                    return strtotime($a->vencimento) > strtotime($b->vencimento) ? 1 : 0;
                });
            }
        }
        $p = view('relatorio_super.relatorio_certificados')
            ->with('data_inicial', $data_inicial)
            ->with('data_final', $data_final)
            ->with('empresas', $temp)
            ->with('status', $status);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4", "landscape");
        $domPdf->render();
        $domPdf->stream("Relatório de certificados.pdf");
    }

    public function empresasContador(Request $request)
    {
        if ($request->contador == '') {
			session()->flash("flash_erro", "Relatório sem registro!");
			return redirect('/relatorioSuper');
		}
        $contador = Contador::findOrFail($request->contador);
        $empresas = Empresa::where('contador_id', $request->contador)
            ->get();
        $dataHoje = date('Y-m-d');
        foreach ($empresas as $e) {
            if ($e->certificado) {
                $infoCertificado = Certificate::readPfx($e->certificado->arquivo, $e->certificado->senha);
                $publicKey = $infoCertificado->publicKey;
                $e->vencimento = $publicKey->validTo->format('Y-m-d');
                $e->vencido = strtotime($dataHoje) > strtotime($e->vencimento);
            }
        }
        $p = view('relatorio_super.empresas_contador')
            ->with('contador', $contador)
            ->with('empresas', $empresas)
            ->with('title', 'Relatório de empresas contador ' . $contador->razao_social);
        // return $p;
        $domPdf = new Dompdf(["enable_remote" => true]);
        $domPdf->loadHtml($p);
        $pdf = ob_get_clean();
        $domPdf->setPaper("A4");
        $domPdf->render();
        $domPdf->stream("Relatório de empresas contador.pdf");
    }
}
