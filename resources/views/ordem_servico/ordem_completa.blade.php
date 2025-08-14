@extends('default.layout', ['title' => 'Ordem de serviço'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-">

                    <h5>Status: <span class="label">{{ strtoupper($ordem->estado) }}</span>
                    </h5>
                    <a href="{{ route('ordemServico.alterarEstado', $ordem->id) }}" class="btn btn-info" href=""><i class="bx bx-refresh"></i>
                    Alterar estado</a>
                    <a class="btn btn-primary" href="{{ route('ordemServico.imprimir', $ordem->id) }}"><i class="bx bx-printer"></i>
                    Imprimir</a>
                    <h5 class="mt-2">Total: <strong>R$ {{ __moeda($ordem->valor) }}</strong> </h5>
                    <h5>Usuário responsável: <strong>{{ $ordem->usuario->nome }}</strong></h5>
                </div>
            </div>
            <div class="card row mt-5">
                {!! Form::open()
                ->post()
                ->route('ordemServico.storeServico')!!}
                <h6 class="mt-3">Serviços</h6>
                <div class="row">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="col-md-4 mt-1">
                        {!! Form::select('servico_id', 'Serviço', [null => 'Selecione'] + $servicos->pluck('nome', 'id')->all())->attrs(['class' => 'select2'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'moeda'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        {!! Form::tel('valor', 'Valor unitário')->attrs(['class' => 'moeda'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        <br>

                        <button type="submit" class="btn btn-info btn-add-servico"><i class="bx bx-plus"></i>Adicionar</button>
                    </div>
                    {!! Form::hidden('status', 'Status')->value('PENDENTE') !!}
                </div>
                {!! Form::close() !!}
                <div class="card-body">
                    <div class="table-responsive">
                        <p class="">Registros: {{ sizeof($ordem->servicos) }}</p>
                        <table class="table mb-0 table-striped table-servico">
                            <thead class="">
                                <tr>
                                    <th>Serviço</th>
                                    <th>Quantidade</th>
                                    <th>Valor unitário</th>
                                    <th>Sub total</th>
                                    <th>Status</th>

                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($ordem)
                                @foreach ($ordem->servicos as $item)
                                <tr>
                                    <td>
                                        <input readonly type="text" name="servico[]" class="form-control" value="{{ $item->servico->nome }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="servico_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                    </td>

                                    <td>
                                        <input readonly type="tel" name="" class="form-control" value="{{ __moeda($item->valor_unitario) }}">
                                    </td>
                                    
                                    <td>
                                        <input readonly type="tel" name="" class="form-control qtd-item" value="{{ __moeda($item->sub_total) }}">
                                    </td>
                                    <td>
                                        @if($item->status)
                                        <button class="btn btn-success btn-sm">FINALIZADO
                                        </button>
                                        @else
                                        <span class="btn btn-warning btn-sm">PENDENTE
                                        </span>
                                        @endif
                                    </td>
                                    <td>
                                        <a href="{{ route('ordemServico.alterarStatusServico', $item->id) }}" class="btn btn-sm btn-info"><i class="bx bx-check"></i></a>
                                        @if(!$item->status)
                                        <a href="{{ route('ordemServico.deleteServico', $item->id) }}" class="btn btn-sm btn-danger btn-delete-row"><i class="bx bx-trash"></i></a>
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>

            <div class="card row">
                {!! Form::open()
                ->post()
                ->route('ordemServico.storeProduto')!!}
                <h6 class="mt-3">Produtos</h6>
                <div class="row">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="col-md-4 mt-1">
                        {!! Form::select('produto_id', 'Produto')->attrs(['class' => 'produto_id'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        {!! Form::tel('quantidade', 'Quantidade')->attrs(['class' => 'qtd qtd_produto'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        {!! Form::tel('valor_unitario', 'Valor unitário')->attrs(['class' => 'moeda valor_produto'])->required() !!}
                    </div>
                    <div class="col-md-2 mt-1">
                        <br>
                        <button type="submit" class="btn btn-info btn-add-servico"><i class="bx bx-plus"></i>Adicionar</button>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="card-body">
                    <div class="table-responsive">
                        <p class="">Registros: {{ sizeof($ordem->produtos) }}</p>
                        <table class="table mb-0 table-striped table-servico">
                            <thead class="">
                                <tr>
                                    <th>Produto</th>
                                    <th>Quantidade</th>
                                    <th>Valor unitário</th>
                                    <th>Sub total</th>
                                    <th>Ações</th>

                                </tr>
                            </thead>
                            <tbody>
                                @isset($ordem)
                                @foreach ($ordem->produtos as $item)
                                <tr>
                                    <td>
                                        <input readonly type="text" name="servico[]" class="form-control" value="{{ $item->produto->nome }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="servico_quantidade[]" class="form-control" value="{{ $item->quantidade }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="servico_quantidade[]" class="form-control" value="{{ __moeda($item->valor_unitario) }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="valor[]" class="form-control qtd-item" value="{{ __moeda($item->sub_total) }}">
                                    </td>
                                    <td>
                                        
                                        <a href="{{ route('ordemServico.deleteProduto', $item->id) }}" class="btn btn-sm btn-danger btn-delete-row"><i class="bx bx-trash"></i></a>

                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card row mt-3">
                {!! Form::open()
                ->post()
                ->route('ordemServico.storeFuncionario') !!}
                <h6 class="mt-3">Funcionários da OS</h6>
                <div class="row">
                    <input type="hidden" value="{{$ordem->id}}" name="ordem_servico_id">
                    <div class="col-md-5">
                        {!! Form::select('funcionario_id', 'Funcionário', [null => 'Selecione'] + $funcionarios->pluck('nome', 'id')->all())->attrs(['class' => 'select2'])->required() !!}
                    </div>
                    <div class="col-md-4">
                        {!! Form::text('funcao', 'Função')->required() !!}
                    </div>
                    <div class="col-md-3">
                        <br>
                        @if(!isset($not_submit))
                        <button type="submit" class="btn btn-info"><i class="bx bx-plus"></i>Adicionar</button>
                        @endif
                    </div>
                    <div class="col-md-1">
                        {!! Form::hidden('celular', 'Celular') !!}
                    </div>
                </div>
                {!! Form::close() !!}

                <div class="card-body">
                    <div class="table-responsive">
                        <p class="">Registros: {{ sizeof($ordem->funcionarios) }}</p>
                        <table class="table mb-0 table-striped table-funcionario">
                            <thead class="">
                                <tr>
                                    <th>Nome</th>
                                    <th>Função</th>
                                    <th>Telefone</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @isset($ordem)
                                @foreach ($ordem->funcionarios as $funcionario)
                                <tr>
                                    <td>
                                        <input readonly type="text" name="nome[]" class="form-control" value="{{ $funcionario->funcionario->nome }}">
                                    </td>
                                    <td>
                                        <input readonly type="text" name="funcao[]" class="form-control" value="{{ $funcionario->funcao }}">
                                    </td>
                                    <td>
                                        <input readonly type="tel" name="telefone[]" class="form-control" value="{{ $funcionario->funcionario->celular }}">
                                    </td>
                                    <td>
                                        <a href="{{ route('ordemServico.deleteFuncionario', $funcionario->id) }}" class="btn btn-sm btn-danger btn-delete-row"><i class="bx bx-trash"></i></a>
                                    </td>
                                </tr>
                                @endforeach
                                @endif
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            <hr>
            <div class="card row mt-3">
                {!! Form::open()->fill(request()->all())->get() !!}
                <h6 class="mt-3">Relatório da OS</h6>
                <div class="row">
                    <div class="col-md-3">
                        <br>
                        <a href="{{ route('ordemServico.addRelatorio', $ordem->id) }}" class="btn btn-info"><i class="bx bx-plus"></i>Adicionar relatório</a>
                    </div>
                    <p class="mt-2">Registros: {{ sizeof($ordem->relatorios)}}</p>
                </div>
                {!! Form::close() !!}
                <div class="card-body">
                    <div class="table-responsive">
                        <table class="table mb-0 table-striped">
                            <thead class="">
                                <tr>
                                    <th>#</th>
                                    <th>Data</th>
                                    <th>Usuário</th>
                                    <th>Ações</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($relatorio as $item)
                                <tr>
                                    <td>{{ $item->ordem_servico_id }}</td>
                                    <td>{{ $item->created_at }}</td>
                                    <td>{{ $item->usuario->nome }}</td>
                                    <td>
                                        <a href="{{ route('ordemServico.deleteRelatorio', $item->id) }}" title="Deletar" class="btn btn-sm btn-danger">
                                            <i class="bx bx-trash"></i>
                                        </a>
                                        <a href="{{ route('ordemServico.editRelatorio', $item->id) }}" title="Editar" class="btn btn-info btn-sm text-white">
                                            <i class="bx bx-edit"></i>
                                        </a>
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

@section('js')
<script type="text/javascript" src="/js/ordem_servico.js"></script>
@endsection

@endsection
