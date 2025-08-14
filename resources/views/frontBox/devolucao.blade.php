@extends('default.layout', ['title' => 'Devolução'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="col">
                    <h6 class="mb-0 text-uppercase">Devolução</h6>
                    {!! Form::open()->fill(request()->all())->get() !!}
                    <div class="row mt-3">
                        <div class="col-md-4">
                            {!! Form::text('nome', 'Nome') !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('nfce', 'NFCe') !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::tel('valor', 'Valor')->attrs(['class' => 'moeda']) !!}
                        </div>
                        <div class="col-md-2">
                            {!! Form::date('start_date', 'Data') !!}
                        </div>
                        <div class="col-md-3 text-left">
                            <br>
                            <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                            <a id="clear-filter" class="btn btn-danger" href="{{ route('vendas.index') }}">Limpar</a>
                        </div>
                    </div>
                    {!! Form::close() !!}
                </div>
            </div>
            <hr>
            <div class="row row-cols-auto g-3" style="margin-left: 60px">
                <div class="col">
                    <a class="btn btn-info px-3 radius-10" href="{{ route('frenteCaixa.index') }}">Frente de Caixa</a>
                </div>
                <div class="col">
                    <a class="btn btn-danger px-3 radius-10" data-bs-toggle="modal" data-bs-target="#modal-inutilizar_nfce">Inutilizar</a>
                </div>
            </div>
            <hr>
            <div class="table-responsive tbl-400">
                <table class="table mb-0 table-striped">
                    <thead>
                        <tr>
                            <th>Cliente</th>
                            <th>Data</th>
                            <th>Tipo de Pagamento</th>
                            <th>Estado</th>
                            <th>Nº NFCe</th>
                            <th>Usuário</th>
                            <th>Valor</th>
                            <th>Ações</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($data as $item)
                        <tr>
                            <td>{{ $item->cliente->razao_social ?? 'Consumidor Final' }}</td>
                            <td>{{ __data_pt($item->created_at, 0) }}</td>
                            <td>{{ $item->getTipoPagamento($item->tipo_pagamento) }}</td>
                            <td>{!! $item->estadoEmissao() !!}</td>
                            <td>{{ $item->numero_nfce ?? '0' }}</td>
                            <td>{{ $item->usuario->nome }}</td>
                            <td>{{ __moeda($item->valor_total) }}</td>
                            <td>
                                <form action="{{ route('frenteCaixa.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                    @method('delete')
                                    @csrf
                                    @if ($item->estado_emissao == 'aprovado')
                                    <a href="#!" onclick="modalCancelar({{ $item->id }}, {{ $item->numero_nfce }})" class="btn btn-danger btn-sm">
                                        <i class="bx bx-error"></i>
                                    </a>
                                    @else
                                    @if (!$item->impedeDelete)
                                    @if ($item->estado != 'APROVADO' && $item->estado != 'CANCELADO')

                                    <button type="button" class="btn btn-delete btn-sm btn-danger">
                                        <i class="bx bx-trash"></i>
                                    </button>

                                    @else
                                    @if ($item->retorno_estoque == 0)
                                    <a class="btn btn-info btn-sm" onclick='swal("Atenção!", "Deseja retornar o estque desta venda?", "").then((sim) => {if(sim){ location.href="/frenteCaixa/retornaEstoque/{{ $item->id }}" }else{return false} })' href="#!">
                                        <i class=""></i>
                                    </a>
                                    @endif
                                    @endif
                                    @endif
                                    @endif
                                </form>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="10" class="text-center">Nada encontrado</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</div>

@include('modals.frontBox._inutilizar_nfce', ['not_submit' => true])
@include('modals.frontBox._cancelar_nfce', ['not_submit' => true])

@endsection
@section('js')
<script type="text/javascript" src="/js/nfce.js"></script>
@endsection

