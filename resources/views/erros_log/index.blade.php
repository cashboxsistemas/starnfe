@extends('default.layout',['title' => 'Lista de Erros'])
@section('content')
<div class="page-content">
    <div class="card">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-2">
                    <div class="col-md-5">
                        {!!Form::select('empresa_id', 'Empresa', ['' => 'Selecione'] + $empresas->pluck('nome', 'id')->all())->attrs(['class' => 'form-select'])
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('start_date', 'Data Inicial')
                        !!}
                    </div>
                    <div class="col-md-3">
                        {!!Form::date('end_date', 'Data Final')
                        !!}
                    </div>
                    <div class="col-md-3 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"><i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('errosLog.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
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
                                        <th>Id</th>
                                        <th>Data</th>
                                        <th>Empresa</th>
                                        <th>Linha</th>
                                        <th>Arquivo</th>
                                        <th>Mensagem</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td>{{ $item->id }}</td>
                                        <td>{{ $item->created_at }}</td>
                                        <td>{{ $item->empresa->nome }}</td>
                                        <td>{{ $item->linha }}</td>
                                        <td>{{ $item->arquivo }}</td>
                                        <td>{{ $item->erro }}</td>
                                        <td>
                                            <form action="{{ route('errosLog.destroy', $item->id) }}" method="post" id="form-{{$item->id}}">
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
                                        <td>
                                        <td colspan="7" class="text-center">Nada encontrado</td>
                                        </td>
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
