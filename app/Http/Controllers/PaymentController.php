<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Empresa;
use App\Models\Plano;
use App\Models\ConfigNota;
use App\Models\Payment;
use App\Models\PlanoEmpresa;
use App\Models\RepresentanteEmpresa;
use App\Models\FinanceiroRepresentante;
class PaymentController extends Controller
{
    public function index(){
        $empresa = Empresa::find(request()->empresa_id);
        $planos = Plano::
        where('visivel', true)
        ->get();

        $config = ConfigNota::where('empresa_id', request()->empresa_id)->first();
        if($config == null){
            session()->flash("flash_erro", "Informe o emitente primeiramente");
            return redirect()->route('configNF.index');
        }

        $plano = $empresa->planoEmpresa;

        if($plano == null){
            session()->flash("mensagem_erro", "Defina um plano!!");
            return redirect()->route('empresas.index');
        }
        $pay = $plano->payment ?? null;
        return redirect()->route('payment.finish');
    }

    public function finish(){
        $empresa = Empresa::find(request()->empresa_id);

        $plano = $empresa->planoEmpresa;

        return view('payment.finish', compact('empresa', 'plano'));
    }

    public function paymentPix(Request $request){
        // \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        if(env("MERCADOPAGO_AMBIENTE") == 'sandbox'){
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        }else{
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN_PRODUCAO"));
        }
        $payment = new \MercadoPago\Payment();

        $payment->transaction_amount = (float)$request->transactionAmount;
        $payment->description = $request->description;
        $payment->payment_method_id = "pix";

        $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();

        $cep = str_replace("-", "", $config->cep);
        $payment->payer = array(
            "email" => $request->payerEmail,
            "first_name" => $request->payerFirstName,
            "last_name" => $request->payerLastName,
            "identification" => array(
                "type" => $request->docType,
                "number" => $request->docNumber
            ),
            "address"=>  array(
                "zip_code" => $cep,
                "street_name" => $config->logradouro,
                "street_number" => $config->numero,
                "neighborhood" => $config->bairro,
                "city" => $config->municipio,
                "federal_unit" => $config->UF
            )
        );

        $payment->save();

        if($payment->transaction_details){
            // print_r($payment);
            // die();
            Payment::where('plano_id', $request->plano_empresa_id)->delete();

            $data = [
                'empresa_id' => $request->empresa_id,
                'plano_id' => $request->plano_empresa_id,
                'valor' => (float)$request->transactionAmount,
                'transacao_id' => (string)$payment->id,
                'status' => $payment->status,
                'forma_pagamento' => 'Pix',
                'link_boleto' => '',
                'status_detalhe' => $payment->status_detail,
                'descricao' => $payment->description,
                'qr_code_base64' => $payment->point_of_interaction->transaction_data->qr_code_base64,
                'qr_code' => $payment->point_of_interaction->transaction_data->qr_code,
            ];

            Payment::create($data);

            $this->setPagamentoRepresentante($config, (float)$request->transactionAmount, 
                'Pix');

            session()->flash("flash_sucesso", "Código pix gerado!");
            return redirect()->route('payment.detail', [(string)$payment->id]);
        }else{

            $err = $this->trataErros($payment->error);
            session()->flash("flash_erro", $err);
            return redirect()->back();
        }

    }

    private function trataErros($arr){
        $cause = $arr->causes[0];
        $errorCode = $cause->code;
        $arrCode = $this->arrayErros($arr);
        return $arrCode[$errorCode];
    }

    private function arrayErros($arr){
        return [
            '2067' => 'Número documento inválido!',
            '13253' => 'Ative o QR code do cadastro!'
        ];
    }

    private function setPagamentoRepresentante($config, $valor, $formaPagamento){

        $rep = RepresentanteEmpresa::
        where('empresa_id', $config->empresa_id)
        ->first();

        if($rep != null){

            $percComissao = $rep->representante->comissao;
            $valorComissao = $valor*($percComissao/100);

            FinanceiroRepresentante::create(
                [
                    'representante_empresa_id' => $rep->id,
                    'forma_pagamento' => $formaPagamento,
                    'valor' => $valor
                ]
            );
        }
    }

    public function detalhesPagamento($code){
        $payment = Payment::where('transacao_id', $code)
        ->first();

        if(env("MERCADOPAGO_AMBIENTE") == 'sandbox'){
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        }else{
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN_PRODUCAO"));
        }
        $payStatus = \MercadoPago\Payment::find_by_id($code);

        if($payStatus->status != $payment->status){
            $this->setarLicenca($payment->plano);
        }

        if($payStatus->status != "approved"){
            $payment->status = $payStatus->status;
            $payment->status_detalhe = $payStatus->status_detail;
            $payment->descricao = $payStatus->description;

            $payment->save();
        }
        
        return view('payment.detalhes_pagamento', compact('payment', 'payStatus'));
    }

    public function paymentCard(Request $request){

        // \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        if(env("MERCADOPAGO_AMBIENTE") == 'sandbox'){
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN"));
        }else{
            \MercadoPago\SDK::setAccessToken(env("MERCADOPAGO_ACCESS_TOKEN_PRODUCAO"));
        }
        $payment = new \MercadoPago\Payment();

        $payment->transaction_amount = (float)$request->transactionAmount;
        $payment->token = $request->token;
        $payment->description = $request->description;
        $payment->installments = (int)$request->installments;
        $payment->payment_method_id = $request->paymentMethodId;
        // $payment->issuer_id = (int)$request->issuer;

        $payer = new \MercadoPago\Payer();
        $payer->email = $request->email;
        $payer->identification = array(
            "type" => $request->docType,
            "number" => $request->docNumber
        );
        $payment->payer = $payer;

        $payment->save();

        if($payment->error){
            session()->flash("mensagem_erro", $payment->error);
            return redirect()->back();
        }else{
            $config = ConfigNota::where('empresa_id', $request->empresa_id)->first();
            Payment::where('plano_id', $request->plano_empresa_id)->delete();
            $data = [
                'empresa_id' => $request->empresa_id,
                'plano_id' => $request->plano_empresa_id,
                'valor' => (float)$request->transactionAmount,
                'transacao_id' => $payment->id,
                'status' => $payment->status,
                'forma_pagamento' => 'Cartão',
                'link_boleto' => $payment->transaction_details->external_resource_url ?? '',
                'status_detalhe' => $payment->status_detail,
                'descricao' => $payment->description,
                'qr_code_base64' => '',
                'qr_code' => '',
            ];

            $this->setPagamentoRepresentante($config, (float)$request->transactionAmount, 
                'Cartão de crédito');

            Payment::create($data);
            if($payment->status == 'approved'){
                $planoEmpresa = PlanoEmpresa::find($request->plano_empresa_id);
                $this->setarLicenca($planoEmpresa);
                session()->flash("flash_sucesso", "Pagamento aprovado com sucesso!");
                return redirect()->route('payment.detail', [(string)$payment->id]);
            }
        }
    }
}
