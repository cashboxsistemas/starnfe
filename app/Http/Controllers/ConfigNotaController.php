<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\NaturezaOperacao;
use App\Models\ConfigNota;
use App\Models\Cidade;
use App\Models\Certificado;
use App\Utils\UploadUtil;
use NFePHP\Common\Certificate;
use Illuminate\Support\Facades\DB;

class ConfigNotaController extends Controller
{
    protected $util;

    public function __construct(UploadUtil $util)
    {
        $this->util = $util;
    }

    public function certificadosFresh()
    {
        $certificados = DB::table('certificados')->get();
        foreach ($certificados as $c) {
            $config = ConfigNota::find($c->empresa_id);
            if ($config) {
                $config->senha = $c->senha;
                $config->arquivo = $c->arquivo;
                $config->save();
            }
        }
    }

    public function removeSenha($id)
    {
        $config = ConfigNota::find($id);
        $config->senha_remover = '';
        $config->save();
        session()->flash("flash_sucesso", "Senha removida!");
        return redirect()->route('configNF.index');
    }

    public function removeLogo()
    {
        $item = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $item->logo = '';
        $item->save();
        session()->flash("flash_sucesso", "Logo removida!");
        return redirect()->back();
    }

    public function index(Request $request)
    {
        $item = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        $naturezas = NaturezaOperacao::where('empresa_id', request()->empresa_id)
            ->get();
        $tiposPagamento = ConfigNota::tiposPagamento();
        $tiposFrete = ConfigNota::tiposFrete();
        $listaCSTCSOSN = ConfigNota::listaCST();
        $listaCSTPISCOFINS = ConfigNota::listaCST_PIS_COFINS();
        $listaCSTIPI = ConfigNota::listaCST_IPI();
        $config = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        $cUF = ConfigNota::estados();
        $infoCertificado = null;
        if ($item != null && $item->arquivo != null) {
            $infoCertificado = $this->getInfoCertificado($item);
        }
        $soapDesativado = !extension_loaded('soap');
        $cidades = Cidade::all();
        return view(
            'config_nota/index',
            compact(
                'config',
                'naturezas',
                'tiposPagamento',
                'tiposFrete',
                'infoCertificado',
                'soapDesativado',
                'listaCSTCSOSN',
                'listaCSTPISCOFINS',
                'listaCSTIPI',
                'cUF',
                'cidades',
                'item'
            )
        );
    }

    private function getInfoCertificado($item)
    {
        try {
            $infoCertificado = Certificate::readPfx($item->arquivo, $item->senha);
            $publicKey = $infoCertificado->publicKey;
            $inicio =  $publicKey->validFrom->format('Y-m-d H:i:s');
            $expiracao =  $publicKey->validTo->format('Y-m-d H:i:s');
            return [
                'serial' => $publicKey->serialNumber,
                'inicio' => \Carbon\Carbon::parse($inicio)->format('d-m-Y H:i'),
                'expiracao' => \Carbon\Carbon::parse($expiracao)->format('d-m-Y H:i'),
                'id' => $publicKey->commonName
            ];
        } catch (\Exception $e) {
            return [];
        }
    }

    public function store(Request $request)
    {
        $this->_validate($request);
        $item = ConfigNota::where('empresa_id', $request->empresa_id)
            ->first();
        if (!__valida_objeto($item)) {
            abort(403);
        }
        try {
            if ($item == null) {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $file_name = $this->util->uploadImage($request, '/configEmitente');
                }
                $request->merge([
                    'pais' => $request->pais ?? '',
                    'cUF' => $request->cUF ?? '',
                    'campo_obs_pedido' => $request->campo_obs_pedido ?? '',
                    'campo_obs_nfe' => $request->campo_obs_nfe ?? '',
                    'certificado_a3' => $request->certificado_a3 ?? 0,
                    'inscricao_municipal' => $request->inscricao_minicipal ?? '',
                    'complemento' => $request->complemento ?? '',
                    'token_ibpt' => $request->token_ibpt ?? '',
                    'percentual_lucro_padrao' => $request->percentual_lucro_padrao ?? 0,
                    'validade_orcamento' => $request->validade_orcamento ?? 0,
                    'casas_decimais' => $request->casas_decimais ?? 2,
                    'logo' => $file_name,
                    'nat_op_padrao' => $request->nat_op_padrao ?? 0,
                    'parcelamento_maximo' => $request->parcelamento_maximo ?? 12,
                    'sobrescrita_csonn_consumidor_final' => $request->sobrescrita_csonn_consumidor_final ?? '',
                    'senha_remover' => $request->senha_remover ? md5($request->senha_remover) : ''
                ]);
                if ($request->hasFile('certificado')) {
                    $file = $request->file('certificado');
                    $temp = file_get_contents($file);
                    $extensao = $file->getClientOriginalExtension();
                    $request->merge([
                        'arquivo' => $temp
                    ]);
                    $cnpj = preg_replace('/[^0-9]/', '', $request->cnpj);
                    $fileName = "$cnpj.$extensao";
                    if (!is_dir(public_path('certificados'))) {
                        mkdir(public_path('certificados'), 0777, true);
                    }
                    if (env("CERTIFICADO_ARQUIVO") == 1) {
                        $file->move(public_path('certificados'), $fileName);
                    }
                }
                ConfigNota::create($request->all());
                session()->flash("flash_sucesso", "Cadastrado com sucesso");
            } else {
                $file_name = '';
                if ($request->hasFile('image')) {
                    $this->util->unlinkImage($item, '/configEmitente');
                    $file_name = $this->util->uploadImage($request, '/configEmitente');
                };
                $request->merge([
                    'pais' => $request->pais ?? '',
                    'cUF' => $request->cUF ?? '',
                    'campo_obs_pedido' => $request->campo_obs_pedido ?? '',
                    'campo_obs_nfe' => $request->campo_obs_nfe ?? '',
                    'certificado_a3' => $request->certificado_a3 ?? 0,
                    'inscricao_municipal' => $request->inscricao_minicipal ?? '',
                    'complemento' => $request->complemento ?? '',
                    'token_ibpt' => $request->token_ibpt ?? '',
                    'percentual_lucro_padrao' => $request->percentual_lucro_padrao ?? 0,
                    'validade_orcamento' => $request->validade_orcamento ?? 0,
                    'casas_decimais' => $request->casas_decimais ?? 2,
                    'parcelamento_maximo' => $request->parcelamento_maximo ?? 12,
                    'logo' => $file_name,
                    'sobrescrita_csonn_consumidor_final' => $request->sobrescrita_csonn_consumidor_final ?? '',
                    'senha_remover' => $request->senha_remover ? md5($request->senha_remover) : ''
                ]);
                if ($request->hasFile('certificado')) {
                    $file = $request->file('certificado');
                    $temp = file_get_contents($file);
                    $extensao = $file->getClientOriginalExtension();
                    $request->merge([
                        'arquivo' => $temp
                    ]);
                    $cnpj = preg_replace('/[^0-9]/', '', $item->cnpj);
                    $fileName = "$cnpj.$extensao";
                    if (!is_dir(public_path('certificados'))) {
                        mkdir(public_path('certificados'), 0777, true);
                    }
                    if (env("CERTIFICADO_ARQUIVO") == 1) {
                        $file->move(public_path('certificados'), $fileName);
                    }
                }
                $item->fill($request->all())->save();
                session()->flash("flash_sucesso", "Emitente atualizado!");
            }

            $value = session('user_logged');
            $value['ambiente'] = $request->ambiente == 1 ? 'Produção' : 'Homologação';
            session()->put('user_logged', $value);
        } catch (\Exception $e) {
            // echo $e->getMessage() . '<br>' . $e->getLine();
            // die;
            session()->flash("flash_erro", "Algo deu Errado" . $e->getMessage());
            __saveLogError($e, request()->empresa_id);
        }
        return redirect()->route('configNF.index');
    }

    private function _validate(Request $request)
    {
        $rules = [
            'cnpj' => 'required',
            'razao_social' => 'required',
            'nome_fantasia' => 'required',
            'ie' => 'required',
            'logradouro' => 'required',
            'numero' => 'required',
            'bairro' => 'required',
            'cep' => 'required',
            'email' => 'required',
            'fone' => 'required',
            'numero_serie_nfe' => 'required',
            'numero_serie_nfce' => 'required',
            'numero_serie_cte' => 'required',
            // 'numero_serie_mdfe' => 'required',
            'ultimo_numero_nfe' => 'required',
            'ultimo_numero_nfce' => 'required',
            'ultimo_numero_cte' => 'required',
            // 'ultimo_numero_mdfe' => 'required',
            'csc' => 'required',
            'csc_id' => 'required|max:10',
            'cidade_id' => 'required',
        ];
        $message = [
            'cnpj.required' => 'Campo Obrigatório',
            'razao_social.required' => 'Campo Obrigatório',
            'nome_fantasia.required' => 'Campo Obrigatório',
            'ie.required' => 'Campo Obrigatório',
            'logradouro.required' => 'Campo Obrigatório',
            'numero.required' => 'Campo Obrigatório',
            'email.required' => 'Campo Obrigatório',
            'bairro.required' => 'Campo Obrigatório',
            'cep.required' => 'Campo Obrigatório',
            'fone.required' => 'Campo Obrigatório',
            'numero_serie_nfe.required' => 'Campo Obrigatório',
            'numero_serie_nfce.required' => 'Campo Obrigatório',
            'numero_serie_cte.required' => 'Campo Obrigatório',
            'numero_serie_mdfe.required' => 'Campo Obrigatório',
            'ultimo_numero_nfe.required' => 'Campo Obrigatório',
            'ultimo_numero_nfce.required' => 'Campo Obrigatório',
            'ultimo_numero_cte.required' => 'Campo Obrigatório',
            'ultimo_numero_mdfe.required' => 'Campo Obrigatório',
            'csc.required' => 'Campo Obrigatório',
            'csc_id.max' => 'Máximo de 10 caracteres.',
            'csc_id.required' => 'Campo Obrigatório',
            'cidade_id.required' => 'Campo Obrigatório'
        ];
        $this->validate($request, $rules, $message);
    }


    public function verificaSenha(Request $request)
    {
        $config = ConfigNota::where('senha_remover', md5($request->senha))
            ->where('empresa_id', $request->empresa_id)
            ->first();
        if ($config != null) {
            return response()->json("ok", 200);
        } else {
            return response()->json("", 401);
        }
    }


    public function deleteCertificado()
    {
        $item = ConfigNota::where('empresa_id', request()->empresa_id)
            ->first();
        try {
            $item->arquivo = '';
            $item->save();
            session()->flash("flash_sucesso", "Certificado Removido!");
        } catch (\Exception $e) {
        }

        return redirect()->route('configNF.index');
    }
}
