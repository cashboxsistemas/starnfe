@extends('default.layout',['title' => 'Filtro Cfop'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInLeft">
                <div class="col-sm-12 col-lg-4 col-md-6 col-xl-4">
                </div>
            </div>
            <h5 class="m-3">Filtro por CFOP</h5>
            <hr>
            <div class="@if(env('ANIMACAO')) animate__animated @endif animate__backInRight" id="kt_user_profile_aside" style="margin-left: 10px; margin-right: 10px;">
                {!!Form::open()
                ->get()
                ->route('enviarXml.filtroCfopGet')
                !!}
                <div class="row mt-4">
                    <div class="col-md-3">
                        {!!Form::date('start_date', 'Data Inicial')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('end_date', 'Data Final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::tel('cfop', 'CFOP')->attrs(['class' => 'cfop'])
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisa</button>
                    </div>
                </div>
                {!!Form::close()!!}

                <br>

                <div class="row mt-3">
                    @isset($itens)
                    <div class="row mt-3">
                        <div class="col-sm-12 col-lg-12 col-md-12 col-xl-12">
                            <div class="row">
                                <div class="col-xl-12">
                                    <div class="table-responsive">
                                        <table class="table mb-0 table-striped" style="max-width: 100%; overflow: scroll">
                                            <thead class="datatable-head">
                                                <tr class="datatable-row" style="left: 0px;">
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 200px;">Produto</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Quantidade</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">Unidade</span></th>
                                                    <th data-field="OrderID" class="datatable-cell datatable-cell-sort"><span style="width: 150px;">R$ Total</span></th>
                                                </tr>
                                            </thead>
                                            @php
                                            $somaValor = 0;
                                            $somaQuantidade = 0;
                                            @endphp
                                            <tbody id="body" class="datatable-body">
                                                @foreach($itens as $i)
                                                <tr class="datatable-row">
                                                    <td class="datatable-cell"><span class="codigo" style="width: 200px;" id="id">{{$i->produto->nome}}</span>
                                                    </td>
                                                    <td class="datatable-cell"><span class="codigo" style="width: 150px;" id="id">{{$i->produto->unidade_venda}}</span>
                                                    </td>
                                                    <td class="datatable-cell"><span class="codigo" style="width: 150px;" id="id">{{number_format($i->quantidade, 2, ',', '.')}}</span>
                                                    </td>
                                                    <td class="datatable-cell"><span class="codigo" style="width: 150px;" id="id">{{ number_format($i->total, 2, ',', '.') }}</span>
                                                    </td>
                                                    @php
                                                    $somaValor += $i->total;
                                                    $somaQuantidade += $i->quantidade;
                                                    @endphp
                                                </tr>
                                                @endforeach
                                            </tbody>
                                        </table>
                                    </div>
                                    <h4 class="mt-4">Quantidade de registros: <strong class="text-info">{{$somaQuantidade}}</strong></h4>
                                    <h4>Valor total de emissão: <strong class="text-info">{{ number_format($somaValor, 2, ',', '.') }}</strong></h4>
                                    @php
                                    $percentual = $somaTotalVendas > 0 ? (100 - ((($somaValor-$somaTotalVendas)/$somaTotalVendas*100)*-1)) : 0;
                                    @endphp
                                    <h4>Percentual: <strong class="text-info">{{ number_format($percentual, 2, ',', '.') }}%</strong></h4>
                                    <form method="get" action="/enviarXml/filtroCfopImprimir">
                                        <!-- <input type="hidden" value="{{json_encode($itens)}}" name="objeto"> -->
                                        <input type="hidden" value="{{$start_date}}" name="dataInicial">
                                        <input type="hidden" value="{{$percentual}}" name="percentual">
                                        <input type="hidden" value="{{$end_date}}" name="dataFinal">
                                        <input type="hidden" value="{{$cfop}}" name="cfop">
                                        <input type="hidden" value="{{$somaTotalVendas}}" name="somaTotalVendas">
                                        <button type="submit" class="btn btn-primary px-5 mt-3">
                                            <i class="bx bx-printer"></i>
                                            Imprimir
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    </div>
                    @endisset
                </div>
            </div>
        </div>
    </div>
</div>

@endsection
