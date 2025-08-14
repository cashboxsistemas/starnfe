@extends('default.layout',['title' => 'Comissão'])
@section('content')
<div class="page-content">
    <div class="card ">
        <div class="card-body p-4">
            <div class="page-breadcrumb d-sm-flex align-items-center mb-3">
            </div>
            <div class="col">
                <h6 class="mb-0 text-uppercase">Vendedor</h6>
                {!!Form::open()->fill(request()->all())
                ->get()
                !!}
                <div class="row">
                    <div class="col-md-3">
                        {!!Form::select('nome', 'Pesquisar por nome', ['' => 'Selecione'] + $vendedor->pluck('nome', 'id')->all())
                        ->attrs(['class', 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('data_inicial', 'Data Inicial')
                        ->attrs(['class', 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('data_final', 'Data Final')
                        ->attrs(['class', 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::date('data_final', 'Data Final')
                        ->attrs(['class', 'select2'])
                        !!}
                    </div>
                    <div class="col-md-2">
                        {!!Form::select('estado', 'Estado', [1 => 'Pendente', 0 => 'Pago'])
                        ->attrs(['class', 'select2'])
                        !!}
                    </div>
                    <div class="col-md-3 text-left ">
                        <br>
                        <button class="btn btn-primary" type="submit"> <i class="bx bx-search"></i>Pesquisar</button>
                        <a id="clear-filter" class="btn btn-danger" href="{{ route('funcionarios.index') }}"><i class="bx bx-eraser"></i>Limpar</a>
                    </div>
                    <div class="col-md-10">
                        <br>
                        <a id="" class="btn btn-primary" href="{{ route('funcionarios.index') }}"><i class="bx bx-dollar"></i>Pagar Comissão</a>
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
                                        <th>Vendedor</th>
                                        <th>% Comissão</th>
                                        <th>valor da Venda</th>
                                        <th>Valor da Comissão</th>
                                        <th>Tipo</th>
                                        <th>Estado</th>
                                        <th>Data</th>
                                        <th>Data de Pagamento</th>
                                        <th>Ações</th>
                                    </tr>
                                </thead>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
