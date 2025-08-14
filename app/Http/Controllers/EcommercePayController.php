<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ConfigEcommerce;
use App\Models\PostBlogEcommerce;
use App\Models\PedidoEcommerce;
use App\Models\CategoriaProdutoEcommerce;
use App\Helpers\PedidoEcommerceHelper;
use Mail;
use Illuminate\Support\Str;
use App\Helpers\StockMove;
use App\Models\Orcamento;
use App\Models\ItemOrcamento;
use App\Models\Cliente;
use App\Models\Produto;
use App\Models\Cidade;

class EcommercePayController extends Controller
{

	public function paymentCartao(Request $request){

		$pedido = PedidoEcommerce::find($request->carrinho_id);
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		$payment = new \MercadoPago\Payment();
		$payment->transaction_amount = $request->transactionAmount;
		$payment->description = $request->description;
		$payment->token = $request->token;
		$payment->installments = (int)$request->installments;
		$payment->payment_method_id = $request->paymentMethodId;

		$docNumber = preg_replace('/[^0-9]/', '', $request->docNumber);

		$payer = new \MercadoPago\Payer();
		$payer->email = $request->email;
		$payer->identification = array(
			"type" => $request->docType,
			"number" => $docNumber
		);
		$payment->payer = $payer;
		$payment->save();

		if($payment->error){

			// $error = $this->trataErros($payment->error);
			// return response()->json($error, 401);

<<<<<<< HEAD
			session()->flash("mensagem_erro", $payment->error);
			// return redirect()->back();
			$link = $config->link;
			$default = $this->getDadosDefault($link);
			return redirect($default['rota'] . '/endereco');
=======
			session()->flash("flash_erro", $payment->error);
			return redirect()->back();
>>>>>>> refs/remotes/origin/main

		}else{
			$pedido->transacao_id = $payment->id;
			$pedido->status_pagamento = $payment->status;
			$pedido->forma_pagamento = 'CARTÃO';
			$pedido->status_detalhe = $payment->status_detail;
			$pedido->hash = Str::random(20);
			$pedido->status = 1;
			$pedido->valor_total = $request->total_pag;

			try{
				$this->sendMail($pedido);
			}catch(\Exception $e){

			}
			
			$pedido->save();

			return redirect('/ecommercePay/finalizado/'.$pedido->hash);
		}
<<<<<<< HEAD
		// print_r($request->all());
=======
		print_r($request->all());
>>>>>>> refs/remotes/origin/main

	}
	public function paymentBoleto(Request $request){

		$pedido = PedidoEcommerce::find($request->carrinho_id);
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		$payment = new \MercadoPago\Payment();

		$payment->transaction_amount = (float)$request->transactionAmount;
		$payment->description = $request->description;
		$payment->payment_method_id = "bolbradesco";

		$cep = str_replace("-", "", $config->cep);

		$docNumber = preg_replace('/[^0-9]/', '', $request->docNumber);

		$payment->payer = array(
			"email" => $request->payerEmail,
			"first_name" => $request->payerFirstName,
			"last_name" => $request->payerLastName,
			"identification" => array(
				"type" => $request->docType,
				"number" => $docNumber
			),
			"address"=>  array(
				"zip_code" => $cep,
				"street_name" => $config->rua,
				"street_number" => $config->numero,
				"neighborhood" => $config->bairro,
<<<<<<< HEAD
				"city" => $config->cidade->nome,
				"federal_unit" => $config->cidade->uf
			)
		);

=======
				"city" => $config->cidade,
				"federal_unit" => $config->uf
			)
		);

		// echo "<pre>";
		// print_r($payment);
		// echo "</pre>";

		// die;

>>>>>>> refs/remotes/origin/main
		$payment->save();

		if($payment->transaction_details){

			$pedido->transacao_id = $payment->id;
			$pedido->status_pagamento = $payment->status;
			$pedido->forma_pagamento = 'Boleto';
			$pedido->valor_total = $request->total_pag;
			$pedido->status_detalhe = $payment->status_detail;
			$pedido->link_boleto = $payment->transaction_details->external_resource_url;
			$pedido->hash = Str::random(20);

			$pedido->status = 1; //criado;
			$pedido->save();

			try{
				$this->sendMail($pedido);
			}catch(\Exception $e){
				echo $e->getMessage();
			}

			return redirect('/ecommercePay/finalizado/'.$pedido->hash);
		}else{
			
<<<<<<< HEAD
			session()->flash("mensagem_erro", $payment->error);
			// return redirect()->back();
			$link = $config->link;
			$default = $this->getDadosDefault($link);
			return redirect($default['rota'] . '/endereco');
=======
			session()->flash("flash_erro", $payment->error);
			return redirect()->back();
>>>>>>> refs/remotes/origin/main
		}

	}

	public function paymentPix(Request $request){

		$pedido = PedidoEcommerce::find($request->carrinho_id);
<<<<<<< HEAD
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

=======
		$config = ConfigEcommerce::where('empresa_id', $pedido->empresa_id)->first();

		// dd($config->mercadopago_access_token);
>>>>>>> refs/remotes/origin/main
		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		$payment = new \MercadoPago\Payment();

<<<<<<< HEAD
=======

>>>>>>> refs/remotes/origin/main
		$payment->transaction_amount = (float)$request->transactionAmount;
		$payment->description = $request->description;
		$payment->payment_method_id = "pix";

		$docNumber = preg_replace('/[^0-9]/', '', $request->docNumber);
<<<<<<< HEAD
=======

>>>>>>> refs/remotes/origin/main
		$cep = str_replace("-", "", $config->cep);
		$payment->payer = array(
			"email" => $request->payerEmail,
			"first_name" => $request->payerFirstName,
			"last_name" => $request->payerLastName,
			"identification" => array(
				"type" => $request->docType,
				"number" => $docNumber
			),
			"address"=>  array(
				"zip_code" => $cep,
				"street_name" => $config->rua,
				"street_number" => $config->numero,
				"neighborhood" => $config->bairro,
<<<<<<< HEAD
				"city" => $config->cidade->nome,
				"federal_unit" => $config->cidade->uf
=======
				"city" => $config->cidade,
				"federal_unit" => $config->uf
>>>>>>> refs/remotes/origin/main
			)
		);

		$payment->save();

		if($payment->transaction_details){

			$pedido->transacao_id = $payment->id;
			$pedido->status_pagamento = $payment->status;
			$pedido->forma_pagamento = 'Pix';
			$pedido->status_detalhe = $payment->status_detail;
			$pedido->link_boleto = '';
			$pedido->valor_total = $request->total_pag;
			$pedido->hash = Str::random(20);

			$pedido->qr_code_base64 = $payment->point_of_interaction->transaction_data->qr_code_base64;
			$pedido->qr_code = $payment->point_of_interaction->transaction_data->qr_code;

			$pedido->status = 1; //criado;
			$pedido->save();

			try{
				$this->sendMail($pedido);
			}catch(\Exception $e){

			}

			return redirect('/ecommercePay/finalizado/'.$pedido->hash);
		}else{
<<<<<<< HEAD
			session()->flash("mensagem_erro", $payment->error);
			$link = $config->link;
			$default = $this->getDadosDefault($link);

			return redirect($default['rota'] . '/endereco');
=======
			session()->flash("flash_erro", $payment->error);
			return redirect()->back();
>>>>>>> refs/remotes/origin/main
		}

	}

	public function finalizado($hash){
		$pedido = PedidoEcommerce::
		where('hash', $hash)
		->first();

		$stockMove = new StockMove();

		foreach($pedido->itens as $item){
			$p = $item->produto->produto;

			$stockMove->downStock($p->id, $item->quantidade, null);
		}

		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();
		$link = $config->link;

		$default = $this->getDadosDefault($link);

		if($pedido->modelo_orcamento){
			return view($default['template'].'/pedido_finalizado_orcamento')
			->with('pedido', $pedido)
			->with('default', $default)
			->with('rota', $default['rota'])
			->with('title', 'Orçamento finalizado');
		}

		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		if($pedido){
			$payStatus = \MercadoPago\Payment::find_by_id($pedido->transacao_id);
			if($payStatus){
				if($payStatus->status != $pedido->status_pagamento){
					$pedido->status_pagamento = $payStatus->status;

					if($payStatus->status == "approved"){
						$pedido->status = 2; 
					}else{
						$pedido->status = 1; 
					}

					$pedido->save();
				}
			}
		}

		$config = $this->getConfig($link);

		return view($default['template'].'/pedido_finalizado')
		->with('pedido', $pedido)
		->with('default', $default)
		->with('cart', true)
		->with('rota', $default['rota'])
		->with('title', 'Pedido finalizado');
	}

	private function getDadosDefault($link){

		$config = $this->getConfig($link);

		$categorias = CategoriaProdutoEcommerce::
		where('empresa_id', $config->empresa_id)
		->get();

		$produtoEcommerceHelper = new PedidoEcommerceHelper();
		$carrinho = $produtoEcommerceHelper->getCarrinho();
		$curtidas = $produtoEcommerceHelper->getProdutosCurtidos();

		$postBlogExists = PostBlogEcommerce::
		where('empresa_id', $config->empresa_id)
		->exists();
		$active = $this->getActive();
		return [
			'config' => $config,
			'template' => $config->tema_ecommerce,
			'categorias' => $categorias,
			'curtidas' => $curtidas,
			'carrinho' => $carrinho,
			'active' => $active,
			'postBlogExists' => $postBlogExists,
			'rota' => '/loja/' . strtolower($config->link)
		];
	}

	private function getConfig($link){
		$config = ConfigEcommerce::
		where('link', $link)
		->first();
		return $config;
	}

	public function consultaPagamento($transacao_id){

		$pedido = PedidoEcommerce::
		where('transacao_id', $transacao_id)
		->first();

		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

		\MercadoPago\SDK::setAccessToken($config->mercadopago_access_token);

		if($pedido){
			$payStatus = \MercadoPago\Payment::find_by_id($pedido->transacao_id);

			if($payStatus->status == "approved"){
				$pedido->status_pagamento = "approved";
				$pedido->status = 2; // confirmado o pagamento;
				$pedido->save();
			}
			return response()->json($payStatus->status, 200);

		}else{
			return response()->json("erro", 404);
		}
	}

	private function getActive(){
		$uri = $_SERVER['REQUEST_URI'];
		$uri = explode("/", $uri);

		$active = "";
		if(isset($uri[3])){
			if($uri[3] == 'categorias') $active = 'categorias';
			elseif($uri[3] == '1') $active = 'categorias';
			elseif($uri[3] == '2') $active = 'categorias';
			// elseif($uri[3] == 'carrinho') $active = 'categorias';
			elseif($uri[3] == 'contato') $active = 'contato';
			elseif($uri[3] == 'blog') $active = 'blog';

			// echo $uri[3];
		}else{
			$active = "home";
		}

		return $active;
	}

	private function sendMail($pedido){
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();
<<<<<<< HEAD
		if(env('MAIL_USERNAME') != ""){
			Mail::send('mail.pedido_finalizado', ['pedido' => $pedido, 
				'config' => $config], function($m) use ($pedido, $config){

					$nomeEmail = $config->nome;
					$m->from(env('MAIL_USERNAME'), $nomeEmail);
					$m->subject('Pedido realizado');
					$m->to($pedido->cliente->email);
				});
		}
=======
		Mail::send('mail.pedido_finalizado', ['pedido' => $pedido, 
			'config' => $config], function($m) use ($pedido, $config){

				$nomeEmail = $config->nome;
				$m->from(env('MAIL_USERNAME'), $nomeEmail);
				$m->subject('Pedido realizado');
				$m->to($pedido->cliente->email);
			});
>>>>>>> refs/remotes/origin/main
	}

	public function finalizaOrcamento(Request $request){
		$pedido = PedidoEcommerce::find($request->carrinho_id);
		$config = ConfigEcommerce::
		where('empresa_id', $pedido->empresa_id)
		->first();

		$pedido->observacao = $request->observacao ?? '';
		$pedido->hash = Str::random(20);
		$pedido->modelo_orcamento = 1;
		$pedido->save();

		$this->criaOrcamento($pedido);

		return redirect('/ecommercePay/finalizado/'.$pedido->hash);
	}


	private function criaOrcamento($pedido){

		$cliente = $this->salvarCliente($pedido);
		$natureza = Produto::firstNatureza($pedido->empresa_id);
		$total = $this->calcTotal($pedido);

		$dt = date("Y-m-d");
		$dataOrcamento = [
			'cliente_id' => $cliente->id,
			'usuario_id' => get_id_user(),
			'frete_id' => null,
			'valor_total' => $total,
			'forma_pagamento' => 'personalizado',
			'email_enviado' => 0,
			'natureza_id' => $natureza->id, 
			'estado' => 'NOVO',
			'observacao' => request()->observacao ?? "",
			'desconto' => 0,
			'transportadora_id' => null,
			'tipo_pagamento' => '99',
			'validade' => date( "Y-m-d", strtotime( "$dt +7 day" )),
			'venda_id' => 0, 
			'empresa_id' => $pedido->empresa_id,
			'acrescimo' => 0,
			'filial_id' => null,
			'vendedor_id' => null,
			'ecommerce' => 1
		];
		$orc = Orcamento::create($dataOrcamento);

		foreach($pedido->itens as $item){
			ItemOrcamento::create([
				'produto_id' => $item->produto->produto->id,
				'orcamento_id' => $orc->id,
				'quantidade' => $item->quantidade,
				'valor' => $item->produto->valor,
				'altura' => 0,
				'largura' => 0,
				'profundidade' => 0,
				'acrescimo_perca' => 0,
				'esquerda' => 0,
				'direita' => 0,
				'inferior' => 0,
				'superior' => 0

			]);

		}

	}

	private function calcTotal($pedido){
		$soma = 0;
		foreach($pedido->itens as $item){
			$quantidade = $item->quantidade;
			$valor = $item->produto->valor;
			$soma += $item->quantidade*$valor;
		}
		return $soma;
	}

	private function salvarCliente($pedido){

		$cliente = $pedido->cliente;
		$endereco = $pedido->endereco;

		$clienteExist = Cliente::
		where('cpf_cnpj', $cliente->cpf)
		->first();

		$cidade = Cidade::
		where('nome', $endereco->cidade)
		->first();

		if($clienteExist == null){
            //criar novo

			$dataCliente = [
				'razao_social' => "$cliente->nome $cliente->sobre_nome",
				'nome_fantasia' => "$cliente->nome $cliente->sobre_nome",
				'bairro' => $endereco->bairro,
				'numero' => $endereco->numero,
				'rua' => $endereco->rua,
				'cpf_cnpj' => $cliente->cpf,
				'telefone' => $cliente->telefone,
				'celular' => $cliente->telefone,
				'email' => $cliente->email,
				'cep' => $endereco->cep,
				'ie_rg' => $cliente->ie,
				'consumidor_final' => 1,
				'limite_venda' => 0,
				'cidade_id' => $cidade != null ? $cidade->id : 1, 
				'contribuinte' => 1,
				'rua_cobranca' => '',
				'numero_cobranca' => '',
				'bairro_cobranca' => '',
				'cep_cobranca' => '',
				'cidade_cobranca_id' => NULL,
				'empresa_id' => $pedido->empresa_id,
				'cod_pais' => 1058,
				'id_estrangeiro' => '',
				'grupo_id' => 0
			];

            // print_r($dataCliente);

			return Cliente::create($dataCliente);

		}else{
            //atualiza endereço

			$clienteExist->rua = $endereco->rua;
			$clienteExist->numero = $endereco->numero;
			$clienteExist->bairro = $endereco->bairro;
			$clienteExist->cep = $endereco->cep;
			$clienteExist->cidade_id = $cidade != null ? $cidade->id : 1;

			$clienteExist->save();
			return $clienteExist;
		}

	}
}
