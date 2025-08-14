@extends('default.layout',['title' => 'Produtos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">orçamentos</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo de pesquisa',
                        [0 => 'Razão Social',
                        1 => 'Nome Fantasia',
                        2 => 'Telefone'])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Cliente')
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
                        {!!Form::select('estado', 'Estado',
                        ["" => 'Todos',
                        'NOVO' => 'Novo',
                        'REPROVADO' => 'Reprovado'
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>

                    @if(empresaComFilial())
                    {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                    @endif

                    <div class="col-md-5 text-left mt-3">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('orcamentoVenda.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <div class="mt-4">
                    <h6>Lista de orçamentos</h6>
                    <p>Número de registros:</p>
                </div>
                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>#</th>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Usuário</th>
                                        @if(empresaComFilial())
                                        <th>Local</th>
                                        @endif
                                        <th>Valor integral</th>
                                        <th>Desconto</th>
                                        <th>Acréscimo</th>
                                        <th>Total</th>
                                        <th>Estado</th>
                                        <th>Data vencimento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>
                                            <input type="checkbox" value="{{$item->id}}" data-email="{{$item->cliente->email}}" class="checkbox" name="" data-status="{{$item->estado_emissao}}" data-numero_nfe="{{$item->numero_nfe}}">
                                        </td>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->cliente->razao_social }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            {{ $item->filial_id ? $item->filial->descricao : 'Matriz' }}
                                        </td>
                                        @endif
                                        <td>{{ __moeda($item->valor_total) }}</td>
                                        <td>{{ $item->desconto }}</td>
                                        <td>{{ $item->acrescimo }}</td>
                                        <td>{{ __moeda($item->valor_total) }}</td>
                                        <td>{{ $item->estado }}</td>
                                        <td>{{ __data_pt($item->validade, 0) }}</td>
                                        <td>
                                            <a type="btn" class="btn btn-info btn-sm" href="{{route('orcamentoVenda.show', $item)}}">
                                                <i class="bx bx-detail"></i>
                                            </a>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="11" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <div class="row g-3 m-2">
                        <div class="col-md-2">
                            <button style="width: 100%" id="btn-imprimir" class="btn btn-info btn-action px-3">Imprimir</button>
                        </div>
                        <div class="col-md-2">
                            <button style="width: 100%" type="button" id="btn-enviar_email" data-bs-toggle="modal" data-bs-target="#modal-emailOrcamento" class="btn btn-warning btn-action">Enviar e-mail</button>
                        </div>
                        <div class="col-md-2 mt-3">
                            <button style="width: 100%" data-bs-toggle="modal" data-bs-target="#modal-whatsApp" class="btn btn-info px-3">WhatsApp</button>
                        </div>
                        <?php  
                        $start_date = str_replace("/", "-", $date);
                        $end_date = str_replace("/", "-", $date);
                        ?>
                        <div class="col-md-3 mt-3">
                            <a style="width: 100%" target="_blank" href="/orcamentoVenda/relatorioItens/{{$start_date}}/{{$end_date}}" class="btn btn-danger px-3">Relatório de compras</a>
                        </div>
                    </div>
                </div>
            </div>
            {!! $data->appends(request()->all())->links() !!}
        </div>
    </div>
</div>

@include('modals._emailOrcamento', ['not_submit' => true])
@include('modals._whatsApp', ['not_submit' => true])


@section('js')
<script type="text/javascript" src="/js/orcamento.js"></script>
@endsection
@endsection
