@extends('relatorios.default')
@section('content')
<h5>Período <strong>{{$data_inicial}} - {{$data_final}}</strong></h5>

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>

		<th width="10%" class="text-left">Data de cadastro</th>
		<th width="25%" class="text-left">Empresa</th>
		<th width="15%" class="text-left">Acessos</th>
		<th width="10%" class="text-left">Total NFe</th>
		<th width="10%" class="text-left">Total NFCe</th>
		<th width="15%" class="text-left">Valor de vendas</th>
		<th width="15%" class="text-left">Plano</th>
		<th width="10%" class="text-left">Valor</th>
	</tr>
</thead>

@foreach($data as $key => $item)
<tr class="@if($key%2 == 0) pure-table-odd @endif">

	<td>{{$item['data_cadastro']}}</td>
	<td>{{$item['empresa']}}</td>
	<td>{{$item['acessos']}}</td>
	<td>{{$item['nfes']}}</td>
	<td>{{$item['nfces']}}</td>
	<td>{{ number_format($item['bruto'], 2, ',', '.') }}</td>
	<td>{{$item['plano_nome']}}</td>
	<td>{{ number_format($item['plano_valor'], 2, ',', '.')}}</td>

</tr>
@endforeach

<h5>Total de registros: <strong>{{sizeof($data)}}</strong></h5>
		
@endsection
