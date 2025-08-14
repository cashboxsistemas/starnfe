@extends('relatorios.default')
@section('content')

@if($d1 && $d2)
<p>Periodo: {{$d1}} - {{$d2}}</p>
@endif

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="25%" class="text-left">Cliente</th>
		<th width="15%" class="text-left">Data</th>
		<th width="10%" class="text-left">Estado</th>
		<th width="30%" class="text-left">Chave</th>
		<th width="15%" class="text-left">Valor</th>
		<th width="15%" class="text-left">Número</th>
		<th width="15%" class="text-left">Tipo</th>
		
	</tr>
</thead>

@php
$soma = 0;
@endphp
@foreach($data as $key => $d)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$d['cliente'] == '' ? 'Consumidor Final' : $d['cliente']}}</td>
	<td>{{$d['data']}}</td>
	<td>{{$d['estado']}}</td>
	<td>{{$d['chave']}}</td>
	<td>R$ {{ number_format($d['valor_total'], 2, ',', '.')}}</td>
	<td>{{$d['numero']}}</td>
	<td>{{ strtoupper($d['tipo']) }}</td>
</tr>
@php
$soma += $d['valor_total'];
@endphp
@endforeach

</table>

<table style="width: 100%;">
	<tbody>
		<tr class="text-left">
			<th width="50%">Total de documentos: {{ sizeof($data) }}</th>
			<th width="50%">Soma: R${{ number_format($soma, 2, ',', '.') }}</th>
		</tr>
	</tbody>
</table>

@endsection
