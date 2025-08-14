@extends('default.layout', ['title' => 'Detalhes'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('caixa.list') }}" type="button" class="btn btn-light btn-sm">
                        <i class="bx bx-arrow-back"></i> Voltar
                    </a>
                </div>
            </div>
            <hr>
            <h5 class="mt-2">Total de vendas: {{ sizeof($vendas) }}</h5>
            <h5 class="mt-3" style="color: rgb(8, 209, 8)">Valor de abertura: <strong>R$ {{__moeda($abertura->valor)}}</strong></h5>
            <div class="mt-3">
                @php $valorEmDinheiro = 0; @endphp
                <h5 style="color: blue">Total por tipo de pagamento:</h5>
                @foreach ($somaTiposPagamento as $key => $tp)
                @if ($tp > 0)
                <div class="col-sm-4 col-lg-4 col-md-6">
                    <div class="card card-custom gutter-b">
                        <div class="card-header">
                            <h3 class="card-title">
                                {{ App\Models\VendaCaixa::getTipoPagamento($key) }}
                            </h3>
                        </div>
                        <div class="card-body">
                            <h4 class="text-success">R$ {{ number_format($tp, 2, ',', '.') }}</h4>
                        </div>
                        @php if($key == '01') $valorEmDinheiro = $tp; @endphp
                    </div>
                </div>
                @endif
                @endforeach
            </div>
            <div class="table-responsive">
                <table class="table mb-0 table-striped">
                    <thead class="">
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de pagamento</th>
                            <th>Estado</th>
                            <th>NFCe / NFe</th>
                            <th>Tipo</th>
                            <th>Valor</th>
                        </tr>
                    </thead>

                    @php
                    $soma = 0;
                    @endphp

                    <tbody>
                        @forelse ($vendas as $item)
                        <tr>
                            <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                            <td>{{ $item->created_at }}</td>
                            <td>
                                <span class="codigo" style="width: 100px;">
                                    @if($item->tipo_pagamento == '99')
                                    Outros
                                    @else
                                    {{$item->getTipoPagamento($item->tipo_pagamento)}}
                                    @endif
                                </span>
                            </td>
                            <td>{{ $item->estado }}</td>
                            <td>{{ $item->numero_nfe > 0 ? $item->NFcNumero : '--' }}</td>
                            <td>{{ $item->tipo }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>
                        </tr>

                        @php
                        if(!$item->rascunho && !$item->consignado)
                        $soma += $item->valor_total;
                        @endphp

                        @empty
                        <tr>
                            <td colspan="8" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            <h2 class="text-info mt-3">Soma de vendas: <strong> {{__moeda($soma)}}</strong></h2>
            @php
            $somaSuprimento = 0;
            $somaSangria = 0;
            @endphp
            <div class="row mt-3">
                <div class="col-12 col-xl-6">
                    <div class="card card-custom gutter-b bg-light-info">
                        <div class="card-body">
                            <h2 class="card-title">Suprimentos:</h2>
                            @if(sizeof($suprimentos) > 0)
                            @foreach($suprimentos as $s)
                            <h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
                            @php
                            $somaSuprimento += $s->valor;
                            @endphp
                            @endforeach
                            @else
                            <h4>R$ 0,00</h4>
                            @endif
                        </div>
                    </div>
                </div>
                <div class="col-12 col-xl-6">
                    <div class="card card-custom gutter-b bg-light-danger">
                        <div class="card-body">
                            <h2 class="card-title">Sangrias:</h2>
                            @if(sizeof($sangrias) > 0)
                            @foreach($sangrias as $s)
                            <h4>Valor: R$ {{number_format($s->valor, 2, ',', '.')}}</h4>
                            @php
                            $somaSangria += $s->valor;
                            @endphp
                            @endforeach
                            @else
                            <h4>R$ 0,00</h4>
                            @endif
                        </div>
                    </div>
                </div>
            </div>
            <div class="card-body">
                <div class="row">
                    <div class="col-6">
                        <h5 class="text-primary">Soma da vendas: <strong>{{ __moeda($soma)}}</strong></h5>
                    </div>
                    <div class="col-6">
                        <h5 class="text-danger">Soma de sangria: <strong>{{ __moeda($somaSangria) }}</strong></h5>
                    </div>
                    <div class="col-6">
                        <h5 class="text-success">Soma de suprimento: <strong>{{ __moeda($somaSuprimento) }}</strong></h5>
                    </div>
                    <div class="col-6">
                        <h5 class="text-info">Resultado caixa: <strong>{{ __moeda($abertura->valor + $valorEmDinheiro - $somaSangria) }}</strong></h5>
                    </div>
                    <div class="col-6">
                        <h5 class="text-warning">Valor em dinheiro gaveta: <strong>{{ __moeda($abertura->valor_dinheiro_caixa) }}</strong></h5>
                    </div>
                </div>
            </div>
            <div class="col">
                <a target="_blank" class="btn btn-primary" href="{{ route('caixa.imprimir', $abertura->id) }}">Imprimir em A4</a>
                <a target="_blank" class="btn btn-info" href="{{ route('caixa.imprimir80', $abertura->id) }}">Imprimir em 80mm</a>
            </div>
        </div>
    </div>
</div>
@endsection
