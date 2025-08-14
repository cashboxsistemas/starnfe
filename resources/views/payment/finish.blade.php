@extends('default.layout', ['title' => 'Pagamento de plano'])
@section('content')
<div class="page-content">
	<div class="card border-top border-0 border-4 border-primary">
		<div class="card-body p-5">

			<h4>Pagamento plano: <strong>{{ $plano->plano->nome }}</strong></h4>
			<div class="row mt-4">
				<div class="col-md-2"></div>
				<div class="col-md-4 row">
					<button type="button" class="btn btn-outline-primary btn-itens active px-6" onclick="selectDiv2('pix')">PIX</button>
				</div>
				<div class="col-md-4 row">
					<button type="button" class="btn btn-outline-primary btn-transporte" onclick="selectDiv2('cartao')">
					CARTÃO DE CRÉDITO</button>
				</div>

			</div>

			<div class="div-pix row mt-5">

				{!! Form::open()->post()->route('payment.pix')->id('form-pix') !!}

				<div class="row">
					<div class="col-md-3 col-12">
						{!! Form::text('payerFirstName', 'Nome')->required() !!}
					</div>
					<div class="col-md-3 col-12">
						{!! Form::text('payerLastName', 'Sobre Nome')->required() !!}
					</div>
					<div class="col-md-3 col-12">
						{!! Form::text('payerEmail', 'Email')->required()->type('email') !!}
					</div>
					<div class="col-md-2 col-12">
						{!! Form::select('docType', 'Tipo Documento')
						->attrs(['data-checkout' => 'docType', 'class' => 'form-select'])
						->required() !!}
					</div>
					<div class="col-md-3 mt-3 col-12">
						{!! Form::text('docNumber', 'Número do documento')
						->attrs(['class' => 'cpf_cnpj', 'data-checkout' => 'docNumber'])
						->required() !!}
					</div>
				</div>

				<input type="hidden" value="{{$plano->id}}" name="plano_empresa_id">
				<input style="visibility: hidden" type="" name="transactionAmount" id="transactionAmount" value="{{$plano->plano->valor}}" />
				<input style="visibility: hidden" value="{{$plano->plano->nome}}" name="description">

				<div class="col-12">
					<button type="submit" class="btn btn-primary px-5 btn-pix" onclick="desativeBtn()">Pagar com PIX</button>
				</div>
				{!! Form::close() !!}
			</div>

			<div class="div-cartao row mt-5 d-none">
				{!! Form::open()->post()->route('payment.card')->id('paymentForm') !!}
				<div class="row">
					<div class="col-md-3 col-12">
						{!! Form::text('cardholderName', 'Titular do cartão')
						->attrs(['data-checkout' => 'cardholderName', 'class' => ''])
						->required() !!}
					</div>

					<div class="col-md-2 col-6">
						{!! Form::select('docType', 'Tipo Documento')
						->attrs(['data-checkout' => 'docType', 'class' => 'form-select'])
						->id('docType2')
						->required() !!}
					</div>

					<div class="col-md-3 col-12">
						{!! Form::text('docNumber', 'Número do documento')
						->attrs(['data-mask' => '00000000000', 'data-checkout' => 'docNumber'])
						->id('docNumber2')
						->required() !!}
					</div>

					<div class="col-md-3 col-12">
						{!! Form::text('payerEmail', 'Email')->required()->type('email') !!}
					</div>

					<div class="col-md-4 col-12 mt-3">
						{!! Form::text('cardNumber', 'Número do cartão')
						->attrs(['class' => 'card_number', 'data-checkout' => 'cardNumber'])
						->id('cardNumber')
						->required() !!}
					</div>
					<div class="col-md-1 mt-6">
						<img id="band-img" style="width: 30px; margin-top: 40px;" src="">
					</div>

					<div class="col-md-3 col-12 mt-3">
						{!! Form::select('installments', 'Parcelas')
						->attrs(['class' => 'form-select', 'data-checkout' => 'installments'])
						->id('installments')
						->required() !!}
					</div>

					<div class="col-md-1 col-6 mt-3">
						{!! Form::text('cardExpirationMonth', 'MM')
						->attrs(['class' => '', 'data-checkout' => 'cardExpirationMonth', 'data-mask' => '00'])
						->id('cardExpirationMonth')
						->required() !!}
					</div>

					<div class="col-md-1 col-6 mt-3">
						{!! Form::text('cardExpirationYear', 'AA')
						->attrs(['class' => '', 'data-checkout' => 'cardExpirationYear', 'data-mask' => '00'])
						->id('cardExpirationYear')
						->required() !!}
					</div>

					<div class="col-md-2 col-6 mt-3">
						{!! Form::text('securityCode', 'Código de segurança')
						->attrs(['class' => '', 'data-checkout' => 'securityCode'])
						->id('securityCode')
						->required() !!}
					</div>


					<select style="visibility: hidden" class="custom-select" id="issuer" name="issuer" data-checkout="issuer">
					</select>

				</div>

				<input style="visibility: hidden" type="" name="transactionAmount" id="transactionAmount" value="{{$plano->plano->valor}}" />
				<input style="visibility: hidden" value="{{$plano->plano->nome}}" name="description">
				<input style="visibility: hidden" name="paymentMethodId" id="paymentMethodId" />
				<input type="hidden" value="{{$plano->id}}" name="plano_empresa_id">

				<div class="col-12">
					<button type="submit" class="btn btn-primary px-5 btn-pix" onclick="desativeBtn()">Pagar com Cartão</button>
				</div>
				{!! Form::close() !!}
			</div>
		</div>
	</div>
</div>
@endsection
@section('js')
<script src="https://secure.mlstatic.com/sdk/javascript/v1/mercadopago.js"></script>

<script type="text/javascript">

	$(function () {
		@if(env("MERCADOPAGO_AMBIENTE") == 'sandbox')
		window.Mercadopago.setPublishableKey('{{env("MERCADOPAGO_PUBLIC_KEY")}}');
		@else
		window.Mercadopago.setPublishableKey('{{env("MERCADOPAGO_PUBLIC_KEY_PRODUCAO")}}');
		@endif

		window.Mercadopago.getIdentificationTypes();

		setTimeout(() => {
			console.clear()
			let s = $('#inp-docType').html()
			console.log(s)

			$('#docType2').html(s)
		}, 2000)

	})

	$('#cardNumber').keyup(() => {
		let cardnumber = $('#cardNumber').val().replaceAll(" ", "");
		if (cardnumber.length >= 6) {
			let bin = cardnumber.substring(0,6);

			window.Mercadopago.getPaymentMethod({
				"bin": bin
			}, setPaymentMethod);
		}
	})

	function setPaymentMethod(status, response) {
		if (status == 200) {
			let paymentMethod = response[0];
			document.getElementById('paymentMethodId').value = paymentMethod.id;

			$('#band-img').attr("src", paymentMethod.thumbnail);
			getIssuers(paymentMethod.id);
		} else {
			alert(`payment method info error: ${response}`);
		}
	}

	function getIssuers(paymentMethodId) {
		window.Mercadopago.getIssuers(
			paymentMethodId,
			setIssuers
			);
	}

	function setIssuers(status, response) {
		if (status == 200) {
			let issuerSelect = document.getElementById('issuer');
			$('#issuer').html('');
			response.forEach( issuer => {
				let opt = document.createElement('option');
				opt.text = issuer.name;
				opt.value = issuer.id;
				issuerSelect.appendChild(opt);
			});

			getInstallments(
				document.getElementById('paymentMethodId').value,
				document.getElementById('transactionAmount').value,
				issuerSelect.value
				);
		} else {
			alert(`issuers method info error: ${response}`);
		}
	}

	function getInstallments(paymentMethodId, transactionAmount, issuerId){
		window.Mercadopago.getInstallments({
			"payment_method_id": paymentMethodId,
			"amount": parseFloat(transactionAmount),
			"issuer_id": parseInt(issuerId)
		}, setInstallments);
	}

	function setInstallments(status, response){
		if (status == 200) {
			document.getElementById('installments').options.length = 0;
			response[0].payer_costs.forEach( payerCost => {
				console.log(payerCost)
				let opt = document.createElement('option');
				opt.text = payerCost.recommended_message;
				opt.value = payerCost.installments;
				document.getElementById('installments').appendChild(opt);
			});
		} else {
			alert(`installments method info error: ${response}`);
		}
	}

	doSubmit = false;
	document.getElementById('paymentForm').addEventListener('submit', getCardToken);
	function getCardToken(event){
		event.preventDefault();
		if(!doSubmit){
			let $form = document.getElementById('paymentForm');
			window.Mercadopago.createToken($form, setCardTokenAndPay);
			return false;
		}
	};

	function setCardTokenAndPay(status, response) {
		if (status == 200 || status == 201) {
			let form = document.getElementById('paymentForm');
			let card = document.createElement('input');
			card.setAttribute('name', 'token');
			card.setAttribute('type', 'hidden');
			card.setAttribute('value', response.id);
			console.log(card)
			form.appendChild(card);
			doSubmit=true;
			spinnerButtons();

			form.submit();
		} else {
			alert("Verify filled data!\n"+JSON.stringify(response, null, 4));
		}
	};

	$('#cardExpirationMonth').keyup(() => {
		let c = $('#cardExpirationMonth').val();
		if(c.length == 2){
			$('#cardExpirationYear').focus()
		}	
	})

	$('#cardExpirationYear').keyup(() => {
		let c = $('#cardExpirationYear').val();
		if(c.length == 2){
			$('#securityCode').focus()
		}	
	})

	function selectDiv2(ref) {
		$('.btn-outline-primary').removeClass('active')
		if (ref == 'pix') {
			$('.div-pix').removeClass('d-none')
			$('.div-cartao').addClass('d-none')
		} else {
			$('.div-pix').addClass('d-none')
			$('.div-cartao').removeClass('d-none')
		}
	}

	function desativeBtn(){
		setTimeout(() => {
			$('.btn').attr('disabled',1)
		}, 100)
	}
</script>
@endsection

