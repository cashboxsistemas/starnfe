<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\ConfigNota;
use App\Models\Devolucao;
use App\Models\EscritorioContabil;
use App\Services\DevolucaoService;

class DevolucaoController extends Controller
{
    public function transmitir(Request $request)
    {

        $item = Devolucao::findOrFail($request->id);
        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        if ($config == null) {
            return response()->json("Configure o emitente", 401);
        }

        try {
            $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
            $devolucao_service = new DevolucaoService([
                "atualizacao" => date('Y-m-d h:i:s'),
                "tpAmb" => (int)$config->ambiente,
                "razaosocial" => $config->razao_social,
                "siglaUF" => $config->cidade->uf,
                "cnpj" => $cnpj,
                "schemes" => "PL_009_V4",
                "versao" => "4.00",
                "tokenIBPT" => "AAAAAAA",
                "CSC" => $config->csc,
                "CSCid" => $config->csc_id
            ], $config);

            if ($item->estado_emissao == 'rejeitado' || $item->estado_emissao == 'novo') {

                $nfe = $devolucao_service->gerarDevolucao($item);
                if (!isset($nfe['erros_xml'])) {

                    $signed = $devolucao_service->sign($nfe['xml']);
                    $resultado = $devolucao_service->transmitir($signed, $nfe['chave']);

                    if ($resultado['erro'] == 0) {
                        $item->chave_gerada = $nfe['chave'];
                        $item->estado_emissao = 'aprovado';
                        $item->numero_gerado = $nfe['nNf'];
                        $config->ultimo_numero_nfe = $nfe['nNf'];
                        $config->save();
                        $item->save();

                        $this->enviarEmailAutomatico($item);

                        $file = file_get_contents(public_path('xml_devolucao/') . $nfe['chave'] . '.xml');
                        importaXmlSieg($file, $item->empresa_id);
                        return response()->json($resultado['success'], 200);
                    } else {
                        $item->estado_emissao = 'rejeitado';
                        $item->chave_gerada = $nfe['chave'];
                        $item->save();
                        return response()->json($resultado['error'], 403);
                    }
                } else {
                    return response()->json($nfe['erros_xml'], 401);
                }
            }
        } catch (\Exception $e) {
            return response()->json($e->getMessage(), 404);
        }
    }

    private function enviarEmailAutomatico($devolucao){
        $escritorio = EscritorioContabil::
        where('empresa_id', $devolucao->empresa_id)
        ->first();

        if($escritorio != null && $escritorio->envio_automatico_xml_contador){
            $email = $escritorio->email;
            Mail::send('mail.xml_automatico', ['descricao' => 'Envio de NFe'], function($m) use ($email, $devolucao){
                $nomeEmpresa = env('MAIL_NAME');
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $nomeEmpresa = str_replace("_", " ",  $nomeEmpresa);
                $emailEnvio = env('MAIL_USERNAME');

                $m->from($emailEnvio, $nomeEmpresa);
                $m->subject('Envio de XML Automático');

                $m->attach(public_path('xml_devolucao/'.$devolucao->chave_gerada.'.xml'));
                $m->to($email);
            });
        }
    }

    public function consultar(Request $request)
    {

        $item = Devolucao::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $devolucao_service = new DevolucaoService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $consulta = $devolucao_service->consultar($item);
        return response()->json($consulta, 200);
    }

    public function cancelar(Request $request){

        $item = Devolucao::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $devolucao_service = new DevolucaoService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $devolucao_service->cancelar($item, $request->motivo);

        if (!isset($nfe['erro'])) {

            $item->estado_emissao = 'cancelado';
            $item->save();

            $file = file_get_contents(public_path('xml_devolucao_cancelada/') . $item->chave_gerada . '.xml');
            importaXmlSieg($file, $config->empresa_id);

            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }

    public function corrigir(Request $request)
    {
        $item = Devolucao::findOrFail($request->id);

        $config = ConfigNota::where('empresa_id', $request->empresa_id)
        ->first();

        $cnpj = preg_replace('/[^0-9]/', '', $config->cnpj);
        $devolucao_service = new DevolucaoService([
            "atualizacao" => date('Y-m-d h:i:s'),
            "tpAmb" => (int)$config->ambiente,
            "razaosocial" => $config->razao_social,
            "siglaUF" => $config->cidade->uf,
            "cnpj" => $cnpj,
            "schemes" => "PL_009_V4",
            "versao" => "4.00",
            "tokenIBPT" => "AAAAAAA",
            "CSC" => $config->csc,
            "CSCid" => $config->csc_id
        ], $config);
        $nfe = $devolucao_service->cartaCorrecao($item, $request->motivo);
        if (!isset($nfe['erro'])) {
            return response()->json($nfe, 200);
        } else {
            return response()->json($nfe['data'], $nfe['status']);
        }
    }
}
