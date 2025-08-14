@extends('relatorios.default')
@section('content')

<br>
<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="10%" class="text-left">Data</th>
		<th width="10%" class="text-left">Id</th>
		<th width="15%" class="text-left">Vendedor</th>
		<th width="15%" class="text-left">Cliente</th>
		<th width="10%" class="text-left">Forma de pagamento</th>
		<th width="10%" class="text-left">Despesas operacionais</th>
		<th width="10%" class="text-left">Desconto</th>
		<th width="10%" class="text-left">Valor venda liq.</th>
		<th width="10%" class="text-left">Valor total</th>
	</tr>
</thead>

@php
$somaPedido = 0;
$somaPedidoLiquido = 0;
$somaPdv = 0;
$somaDesconto = 0;
@endphp

<tbody>
	@foreach($vendas as $key => $v)
	<tr class="@if($key%2 == 0) pure-table-odd @endif">
		<td>{{\Carbon\Carbon::parse($v->created_at)->format('d/m/Y H:i')}}</td>
		<td>{{$v->id}}</td>
		<td>{{$v->vendedor()}}</td>
		<td>{{$v->cliente ? $v->cliente->razao_social : 'Consumidor final'}}</td>
		@if(isset($v->cpf))
		<td>{{$v->getTipoPagamento2()}}</td>
		@else
		<td>{{$v->getTipoPagamento()}}</td>
		@endif
		@if($v->tbl == 'pdv')
		<td>R$ 0,00</td>

		<td>R$ {{number_format($v->desconto, 2, ',', '.')}}</td>
		<td>R$ {{number_format($v->valor_total, 2, ',', '.')}}</td>
		<td>R$ {{number_format($v->valor_total+$v->desconto, 2, ',', '.')}}</td>
		@else
		<td>R$ {{ $v->valorDespesaOperacionais() }}</td>
		<td>R$ {{number_format($v->desconto, 2, ',', '.')}}</td>
		<td>R$ {{ number_format($v->valorLiquido(), 2, ',', '.') }}</td>
		<td>R$ {{number_format($v->valor_total, 2, ',', '.')}}</td>
		@endif

	</tr>

	@php
	if(!isset($v->cpf)){
		$somaPedido += $v->valor_total;
		$somaPedidoLiquido += $v->valorLiquido();
	}else{
		$somaPdv += $v->valor_total;
	}
	$somaDesconto += $v->desconto
	@endphp
	@endforeach
</tbody>

</table>

<table style="width: 100%;">
	<tbody>
		<tr>
			<th>Soma Pedido: <strong>{{number_format($somaPedido, 2, ',', '.')}}</strong></th>
			<th>Soma Pedido Liquido: <strong>{{number_format($somaPedidoLiquido, 2, ',', '.')}}</strong></th>
		</tr>
		<tr>
			<th>Soma PDV: <strong>{{number_format($somaPdv, 2, ',', '.')}}</strong></th>
			<th>Total: <strong>{{number_format($somaPedidoLiquido + $somaPdv, 2, ',', '.')}}</strong></th>
		</tr>
		<tr>
			<th>Soma Desconto: <strong>{{number_format($somaDesconto, 2, ',', '.')}}</strong></th>
			<th></th>
		</tr>
	</tbody>
</table>
@endsection
