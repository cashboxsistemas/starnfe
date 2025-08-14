@if($contaReceber > 0)
<a class="dropdown-item alert-item" href="{{ route('conta-receber.index') }}">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-success text-success"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Conta a receber</h6>
			<small>em atraso</small>
			<p class="msg-info">R$ {{ __moeda($contaReceber) }}</p>
		</div>
	</div>
</a>
@endif

@if($contaPagar > 0)
<a class="dropdown-item alert-item" href="{{ route('conta-pagar.index') }}">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-danger text-danger"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Conta a pagar</h6>
			<small>em atraso</small>
			<p class="msg-info">R$ {{ __moeda($contaPagar) }}</p>
		</div>
	</div>
</a>
@endif

@if(sizeof($produtosComAlertaEstoque) > 0)
@foreach($produtosComAlertaEstoque as $p)
<a class="dropdown-item alert-item" href="{{ route('produtos.index') }}">
	<div class="d-flex align-items-center">
		<div class="notify bg-light-danger text-danger"><i class="bx bx-money"></i>
		</div>
		<div class="flex-grow-1">
			<h6 class="msg-name">Produto com alerta em estoque</h6>
			<small></small>
			<p class="msg-info">{{ $p['nome'] }} - estoque: {{ $p['estoque'] }}</p>
		</div>
	</div>
</a>
@endforeach
@endif