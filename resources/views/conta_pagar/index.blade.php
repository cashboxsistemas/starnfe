@extends('default.layout',['title' => 'Contas a Pagar'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('conta-pagar.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova conta
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Contas a pagar</h6>
                {!!Form::open()
                ->fill(request()
                ->all())
                ->get() !!}
                <div class="row">
                    <div class="col-md-4">
                        {!!Form::select('fornecedor_id', 'Fornecedor')
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('type_search', 'Tipo de pesquisa',
                        [
                        'created_at' => 'Data de cadastro',
                        'data_vencimento' => 'Data de vencimento',
                        'data_pagamento' => 'Data de pagamento',
                        ])->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="col-md-2">
                        {!!Form::date('start_date', 'Data inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('end_date', 'Data final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('status', 'Estado',
                        [
                        '' => 'Todos',
                        '1' => 'Pago',
                        '0' => 'Pendente',
                        ])->attrs(['class' => 'form-select'])
                        !!}
                    </div>

                    <div class="mt-3">
                        @if(empresaComFilial())
                        {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                        @endif
                    </div>

                    <div class="col-md-4 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('conta-pagar.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th width="">Fornecedor</th>
                                        <th width="">Categoria</th>
                                        @if(empresaComFilial())
                                        <th><span style="width: 150px;">Local</span></th>
                                        @endif
                                        <th width="">Valor integral</th>
                                        <th width="">Valor pago</th>
                                        <th width="">Data de cadastro</th>
                                        <th width="">Data de vencimento</th>
                                        <th width="">Data de pagamento</th>
                                        <th width="">Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->fornecedor ? $item->fornecedor->razao_social : '--' }}</td>
                                        <td>{{ $item->categoria->nome }}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                        </td>
                                        @endif
                                        <td>{{ __moeda($item->valor_integral) }}</td>
                                        <td>{{ __moeda($item->valor_pago) }}</td>
                                        <td>{{ __data_pt($item->created_at) }}</td>
                                        <td>{{ __data_pt($item->data_vencimento, false) }}</td>
                                        <td>{{ $item->status ? __data_pt($item->data_pagamento, false) : '--' }}</td>
                                        <td>
                                            @if($item->status)
                                            <span class="btn btn-success position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-like"></i> Pago
                                            </span>
                                            @else
                                            <span class="btn btn-warning position-relative me-lg-5 btn-sm">
                                                <i class="bx bx-error"></i> Pendente
                                            </span>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('conta-pagar.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @if(!$item->status)
                                                <a title="Editar" href="{{ route('conta-pagar.edit', $item) }}" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                <a title="Pagar conta" href="{{ route('conta-pagar.pay', $item) }}" class="btn btn-success btn-sm text-white">
                                                    <i class="bx bx-dollar"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
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
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>
@endsection
