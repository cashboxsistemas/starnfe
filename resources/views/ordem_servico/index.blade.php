@extends('default.layout',['title' => 'Ordem de serviço'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                    <a href="{{ route('ordemServico.create')}}" type="button" class="btn btn-success">
                        <i class="bx bx-plus"></i> Nova ordem de serviço
                    </a>
                </div>
            </div>
            <hr>
            <div class="col mt-2">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-5">
                        {!!Form::select('cliente_id', 'Pesquisar por cliente')
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
                        {!!Form::select('estado', 'Estado', [0 => 'Pendente', 1 => 'Aprovado', 2 => 'Reprovado', 3 => 'Finalizado'])->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-6 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('ordemServico.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <h6 class="mt-4">Lista de ordem de serviço</h6>
                <p>Total: {{sizeof($data)}}</p>
                <div class="card">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>Razão social</th>
                                        <th>Valor</th>
                                        <th>Data</th>
                                        <th>Usuário</th>
                                        <th>Estado</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->cliente->razao_social }}</td>
                                        <td>{{ $item->valor }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->usuario->nome }}</td>
                                        <td>
                                            @if($item->estado == 'pd')
                                            <a class="btn btn-warning btn-sm">PENDENTE</a>
                                            @elseif($item->estado == 'ap')
                                            <a class="btn btn-success btn-sm">APROVADO</a>
                                            @elseif($item->estado == 'rp')
                                            <a class="btn btn-danger btn-sm">REPROVADO</a>
                                            @else
                                            <a class="btn btn-success btn-sm">FINALIZADO</a>
                                            @endif
                                        </td>
                                        <td>
                                            <form action="{{ route('ordemServico.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
                                                @method('delete')
                                                @csrf
                                                <button class="btn btn-danger btn-delete btn-sm"><i class="bx bx-trash"></i></button>
                                                <a href="{{ route('ordemServico.completa', $item) }}" title="Ver" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-detail"></i>
                                                </a>
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
            {!! $data->appends(request()->all())->links() !!}

        </div>
    </div>
</div>
@endsection
