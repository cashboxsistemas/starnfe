@extends('default.layout',['title' => 'Pedidos Ecommerce'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
                <div class="ms-auto">
                </div>
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Pesquisar Vendas</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row mt-3">
                    <div class="col-md-4">
                        {!!Form::select('cliente_id', 'Cliente')->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::tel('transacao_id', 'Transação ID')
                        ->attrs(['class' => ''])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('datainicial', 'Data Inicial')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('datafinal', 'Data Final')
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('estado', 'Estado',
                        [0 => 'Disponiveis',
                        1 => 'Rejeitadas',
                        2 => 'Canceladas',
                        3 => 'Aprovadas',
                        4 => 'Todos'])
                        ->attrs(['class' => 'select2'])
                        !!}
                    </div>
                    <div class="col-md-5 text-left">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('pedidosEcommerce.index') }}"><i class="bx bx-eraser"></i> Limpar</a>
                    </div>
                </div>
                {!!Form::close()!!}
                <hr />
                <h6>Lista de Pedidos</h6>
                <p>Registros:</p>
                <div class="row">
                </div>
                <div class="card mt-3">
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table mb-0 table-striped">
                                <thead class="">
                                    <tr>
                                        <th>ID</th>
                                        <th>Cliente</th>
                                        <th>Data</th>
                                        <th>Forma de Pagamento</th>
                                        <th>Estado de Pagamento</th>
                                        <th>Estado de Envio</th>
                                        <th>Nfe</th>
                                        <th>Valor</th>
                                        <th>Frete</th>
                                        <th>Valor Total</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @forelse ($data as $item)
                                    <tr>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td></td>
                                        <td>
                                            <form action="" method="post" id="">
                                                @method('delete')
                                                <a href="" class="btn btn-warning btn-sm text-white">
                                                    <i class="bx bx-edit"></i>
                                                </a>
                                                @csrf
                                                <button type="button" class="btn btn-delete btn-sm btn-danger">
                                                    <i class="bx bx-trash"></i>
                                                </button>
                                                <a href="" class="btn btn-info btn-sm text-white">
                                                    <i class="bx bx-detail"></i>
                                                </a>
                                            </form>
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
                        {!! $data->appends(request()->all())->links() !!}
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
