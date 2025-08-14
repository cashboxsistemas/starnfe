@extends('default.layout',['title' => 'Apuração mensal'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('apuracaoMensal.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova apuração
                    </a>
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Apuração mensal</h6>
                {!! Form::open()->fill(request()->all())->get() !!}
                <div class="row mt-2">
                    <div class="col-md-5">
                        {!! Form::text('nome', 'Nome') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('start_date', 'Data inicial') !!}
                    </div>
                    <div class="col-md-2">
                        {!! Form::date('end_date', 'Data final') !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('apuracaoMensal.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!! Form::close() !!}
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Funcionário</th>
                                        <th>Data de registro</th>
                                        <th>Valor final</th>
                                        <th>Mês/Ano</th>
                                        <th>Adicionado em contas a pagar</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse($data as $item)
                                    <tr>
                                        <td>{{ $item->funcionario->nome }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ __moeda($item->valor_final) }}</td>
                                        <td> {{strtoupper($item->mes)}}/{{$item->ano}} </td>
                                        <td>
                                            <span class="codigo" style="width: 150px;" id="id">
                                                @if($item->conta_pagar_id == 0)
                                                <span class="btn btn-danger btn-sm">Não</span>
                                                @else
                                                <span class="btn btn-success btn-sm">Sim</span>
                                                <a target="_blank" href="/contasPagar/edit/{{$item->conta_pagar_id}}">#{{$item->conta_pagar_id}}</a>
                                                @endif
                                            </span>
                                        </td>
                                        <td>
                                            <form action="{{ route('apuracaoMensal.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                            </form>
                                        </td>
                                    </tr>
                                    @empty
                                    <tr>
                                        <td colspan="6" class="text-center">Nada encontrado</td>
                                    </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
