@extends('relatorios.default')
@section('content')

<table class="table-sm table-borderless"
style="border-bottom: 1px solid rgb(206, 206, 206); margin-bottom:10px;  width: 100%;">
<thead>
	<tr>
		<th width="50%" class="text-left">Produto</th>
		<th width="15%" class="text-left">Valor de venda</th>
		<th width="15%" class="text-left">Valor de compra</th>
		<th width="20%" class="text-left">Categoria</th>
		<th width="20%" class="text-left">CFOP</th>
	</tr>
</thead>

@foreach($produtos as $key => $p)
<tr class="@if($key%2 == 0) pure-table-odd @endif">
	<td>{{$p->nome}}</td>
	<td>{{number_format($p->valor_venda, 2)}}</td>
	<td>{{number_format($p->valor_compra, 2)}}</td>
	<td>{{$p->categoria->nome}}</td>
	<td>{{$cfop}}</td>

</tr>
@endforeach

</table>

@endsection
