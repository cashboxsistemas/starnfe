@extends('default.layout',['title' => 'Produtos'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto col-12">
                    <a href="{{ route('produtos.create')}}" type="button" class="btn btn-success col-12 col-lg-2">
                        <i class="bx bx-plus"></i> Novo produto
                    </a>
                    <a href="{{ route('produtos.exportacaoBalanca')}}" type="button" class="btn btn-dark col-12 col-lg-2">
                        <i class="bx bx-upvote"></i> Exportar balança
                    </a>
                    <a href="{{ route('produtos.import')}}" type="button" class="btn btn-warning col-12 col-lg-2">
                        <i class="bx bx-file"></i> Importar produtos
                    </a>
                    <a href="{{ route ('divisaoGrade.index')}}" type="button" class="btn btn-info col-12 col-lg-2">
                        <i class="bx bx-dice-6"></i> Divisão de grade
                    </a>

                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Produtos</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-2">
                        {!!Form::select('tipo', 'Tipo de pesquisa',
                        ['nome' => 'Descrição',
                        'referencia' => 'Referência',
                        'codBarras' => 'Código de barras'
                        ])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-4">
                        {!!Form::text('nome', 'Pesquisar por nome') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('categoria_id', 'Categoria', ['' => 'Todas'] + $categorias->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('marca_id', 'Marca', ['' => 'Todas'] + $marcas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select']) !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::select('estoque', 'Estoque', ['0' => '--', '1' => 'Positivo', '-1' => 'Negativo'])->attrs(['class' => 'form-select']) !!}
                    </div>

                    <div class="mt-3">
                        @if(empresaComFilial())
                        {!! __view_locais_select_filtro("Local", isset($filial_id) ? $filial_id : '') !!}
                        @endif
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('produtos.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                </div>

                {!!Form::close()!!}

                <hr />
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive tbl-400">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th></th>
                                        <th>Ações</th>
                                        <th width="30%">Descrição</th>
                                        <th width="20%">Valor de compra</th>
                                        <th width="20%">Valor de venda</th>
                                        <th width="20%">Un. compra/venda</th>
                                        <th width="20%">Data cadastro</th>
                                        <th>Gerenciar estoque</th>
                                        @if(empresaComFilial())
                                        <th>Disponibilidade</th>
                                        @endif
                                        <th>Estoque atual</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $p)
                                    <tr>
                                        <td><img class="img-round" src="{{ $p->img }}"></td>
                                        <td>
                                            <div class="dropdown">
                                                <button class="btn btn-sm btn-secondary dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">Ações</button>
                                                <ul class="dropdown-menu" style="z-index: 999">
                                                    <form action="{{ route('produtos.destroy', $p->id) }}" method="post" id="form-{{$p->id}}">
                                                        @method('delete')
                                                        @csrf
                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('produtos.edit', $p->id) }}">Editar</a>
                                                        </li>

                                                        <li>
                                                            <button class="dropdown-item btn-delete">Apagar</button>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('produtos.movimentacao', $p->id) }}">Movimentação</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('produtos.duplicar' ,$p->id) }}">Duplicar Produto</a>
                                                        </li>

                                                        <li>
                                                            <a class="dropdown-item" href="{{ route('produtos.etiqueta', $p->id) }}">Código de Barras</a>
                                                        </li>
                                                    </form>
                                                </ul>
                                            </div>
                                        </td>
                                        <td>{{$p->nome}} {{$p->str_grade}}</td>
                                        <td>{{ __moeda($p->valor_compra) }}</td>
                                        <td>{{ __moeda($p->valor_venda) }}</td>
                                        <td>{{ $p->unidade_compra}}/{{ $p->unidade_venda}}</td>
                                        <td>{{ __data_pt($p->created_at) }}</td>
                                        <td>{{ $p->gerenciar_estoque == 0 ? 'Não' : 'Sim' }}</td>
                                        @if(empresaComFilial())
                                        <td>
                                            <span class="codigo" style="width: 200px;">
                                                {!! $p->locais_produto() !!}
                                            </span>
                                        </td>
                                        @endif
                                        <td>
                                            @if(empresaComFilial())
                                            {{ $p->estoquePorLocal($p->locais) }}
                                            @else
                                            {{ $p->estoquePorLocal($filial_id) }}
                                            @endif
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
